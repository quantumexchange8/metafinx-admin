<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSubscription;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletNet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $payment = Payment::where('status', 'Success');
        $withdrawal = clone $payment;

        $totalDeposit = $payment->where('type', 'Deposit')->sum('amount');
        $totalWithdrawal = $withdrawal->where('type', 'Withdrawal')->sum('amount');
        $totalTransaction = $totalDeposit + $totalWithdrawal;
        $totalWalletBalance = Wallet::sum('balance');

        $pendingMemberCount  = User::query()
        ->where('kyc_approval', 'pending')
        ->count();

        $pendingTransactionCount  = Payment::query()
        ->where('status', 'Processing')
        ->count();

        $pendingTransactions  = Payment::query()
            ->where('status', 'Processing')
            ->with(['user:id,name,email'])
            ->select('id', 'user_id', 'transaction_id', 'status', 'amount', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Get the media URL for 'profile_photo' for each user
        $pendingTransactions->each(function ($user_transaction) {
            $user_transaction->user->profile_photo_url = $user_transaction->user->getFirstMediaUrl('profile_photo');
        });

        $currentTotalInvestment = InvestmentSubscription::whereIn('status', ['CoolingPeriod', 'OngoingPeriod'])
            ->sum('amount');

        return Inertia::render('Dashboard', [
            'totalDeposit' => floatval($totalDeposit),
            'totalWithdrawal' => floatval($totalWithdrawal),
            'totalTransaction' => $totalTransaction,
            'totalWalletBalance' => floatval($totalWalletBalance),
            'totalInvestment' => InvestmentSubscription::all()->sum('amount'),
            'currentTotalInvestment' => number_format($currentTotalInvestment, 2),
            'totalMembers' => User::where('status', 1)->count(),
            'pendingMemberCount' => $pendingMemberCount,
            'pendingTransactions' => $pendingTransactions,
            'pendingTransactionCount' => $pendingTransactionCount,
        ]);
    }

    public function getTotalMembersByDays(Request $request)
    {
        $selectedPlans = User::with('rank')
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = $request->input('month');
                $year = $request->input('year');
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
            })
            ->select(
                DB::raw('DAY(created_at) as day'),
                'setting_rank_id',
                DB::raw('count(*) as users_count')
            )
            ->groupBy('day', 'setting_rank_id')
            ->get();

        // Get unique setting_rank_ids to create datasets
        $uniqueSettingRankIds = $selectedPlans->pluck('setting_rank_id')->unique();

        $year = $request->year;
        $month = $request->month;
        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        $backgroundColors = [1 => '#FFB2AB', 2 => '#FF2D55', 3 => '#FEC84B', 4 => '#F79009'];

        // Loop through each unique setting_rank_id and create a dataset
        foreach ($uniqueSettingRankIds as $settingRankId) {
            $dataForRank = $selectedPlans->where('setting_rank_id', $settingRankId);

            $dataset = [
                'label' => $dataForRank->first()->rank->name,
                'data' => array_map(function ($day) use ($dataForRank) {
                    return $dataForRank->firstWhere('day', $day)->users_count ?? 0;
                }, $chartData['labels']),
                'backgroundColor' => $backgroundColors[$settingRankId],
                'borderRadius' => PHP_INT_MAX,
                'barPercentage' => 0.4
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalTransactionByDays(Request $request)
    {
        $transactions = Payment::query()
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = $request->input('month');
                $year = $request->input('year');
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
            })
            ->where('status', '=', 'Success')
            ->select(
                DB::raw('DAY(created_at) as day'),
                'type',
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('day', 'type')
            ->get();

        // Get unique type to create datasets
        $uniqueTransactionType = $transactions->pluck('type')->unique();
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month;
        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        $backgroundColors = ['Deposit' => '#fdb022', 'Withdrawal' => '#FF2D55'];

        // Loop through each unique type and create a dataset
        foreach ($uniqueTransactionType as $transactionType) {
            $transactionData = $transactions->where('type', $transactionType);

            $dataset = [
                'label' => $transactionData->first()->type,
                'data' => array_map(function ($day) use ($transactionData) {
                    return $transactionData->firstWhere('day', $day)->amount ?? 0;
                }, $chartData['labels']),
                'borderColor' => $backgroundColors[$transactionType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalTransactionByMonths(Request $request)
    {
        $transactions = Payment::query()
            ->when($request->filled('year'), function ($query) use ($request) {
                $year = $request->input('year');
                $query->whereYear('created_at', $year);
            })
            ->where('status', '=', 'Success')
            ->select(
                DB::raw('MONTH(created_at) as month'), // Change DAY to MONTH
                'type',
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month', 'type') // Change 'day' to 'month'
            ->get();

        $uniqueTransactionType = $transactions->pluck('type')->unique();

        // Initialize the chart data structure with short month names as labels
        $shortMonthNames = [];
        for ($month = 1; $month <= 12; $month++) {
            $shortMonthNames[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        $chartData = [
            'labels' => $shortMonthNames,
            'datasets' => [],
        ];

        $backgroundColors = ['Deposit' => '#fdb022', 'Withdrawal' => '#FF2D55'];

        foreach ($uniqueTransactionType as $transactionType) {
            $transactionData = $transactions->where('type', $transactionType);

            $dataset = [
                'label' => $transactionData->first()->type,
                'data' => array_map(function ($month) use ($transactionData) {
                    return $transactionData->firstWhere('month', $month)->amount ?? 0;
                }, range(1, 12)), // Use month numbers 1-12
                'borderColor' => $backgroundColors[$transactionType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalWalletBalanceByDays(Request $request)
    {
        $wallet_balances = DB::table('wallet_nets')
            ->leftJoin('wallets', 'wallet_nets.wallet_id', '=', 'wallets.id')
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = $request->input('month');
                $year = $request->input('year');
                $query->whereYear('wallet_nets.created_at', $year)
                    ->whereMonth('wallet_nets.created_at', $month);
            })
            ->where('wallet_nets.deleted_at', null)
            ->select(
                DB::raw('DAY(wallet_nets.created_at) as day'),
                'wallets.name as wallet_name',
                'wallets.type as wallet_type',
                DB::raw('SUM(wallet_nets.total_balance) as balance')
            )
            ->groupBy('day', 'wallet_name', 'wallet_type')
            ->get();

        // Get unique type to create datasets
        $uniqueWalletType = $wallet_balances->pluck('wallet_type')->unique();

        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month;
        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        $backgroundColors = ['internal_wallet' => '#FF2D55', 'musd_wallet' => '#fdb022'];

        // Loop through each unique type and create a dataset
        foreach ($uniqueWalletType as $walletType) {
            $wallet = $wallet_balances->where('wallet_type', $walletType);

            $dataset = [
                'label' => $wallet->first()->wallet_name,
                'data' => array_map(function ($day) use ($wallet) {
                    return $wallet->firstWhere('day', $day)->balance ?? 0;
                }, $chartData['labels']),
                'borderColor' => $backgroundColors[$walletType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalWalletBalanceByMonths(Request $request)
    {
        $wallet_balances = DB::table('wallet_nets')
            ->leftJoin('wallets', 'wallet_nets.wallet_id', '=', 'wallets.id')
            ->when($request->filled('year'), function ($query) use ($request) {
                $year = $request->input('year');
                $query->whereYear('wallet_nets.created_at', $year);
            })
            ->where('wallet_nets.deleted_at', null)
            ->select(
                DB::raw('MONTH(wallet_nets.created_at) as month'),
                'wallets.name as wallet_name',
                'wallets.type as wallet_type',
                DB::raw('SUM(wallet_nets.total_balance) as total_balance')
            )
            ->groupBy('month', 'wallet_name', 'wallet_type')
            ->get();

        $uniqueWalletType = $wallet_balances->pluck('wallet_type')->unique();

        // Initialize the chart data structure with short month names as labels
        $shortMonthNames = [];
        for ($month = 1; $month <= 12; $month++) {
            $shortMonthNames[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        $chartData = [
            'labels' => $shortMonthNames,
            'datasets' => [],
        ];

        $backgroundColors = ['internal_wallet' => '#FF2D55', 'musd_wallet' => '#fdb022'];

        foreach ($uniqueWalletType as $walletType) {
            $wallet = $wallet_balances->where('wallet_type', $walletType);

            $dataset = [
                'label' => $wallet->first()->wallet_name,
                'data' => array_map(function ($month) use ($wallet) {
                    return $wallet->firstWhere('month', $month)->total_balance ?? 0;
                }, range(1, 12)), // Use month numbers 1-12
                'borderColor' => $backgroundColors[$walletType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalInvestmentByDays(Request $request)
    {
        $subscriptions = InvestmentSubscription::query()
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = $request->input('month');
                $year = $request->input('year');
                $query->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
            })
            ->select(
                DB::raw('DAY(created_at) as day'),
                'status', // No need for CASE statement
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('day', 'status')
            ->get();

        // Get unique type to create datasets
        $uniqueSubscriptionsType = $subscriptions->pluck('status')->unique();

        $year = Carbon::now()->year;
        $month = $request->month;
        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        $backgroundColors = ['CoolingPeriod' => '#32ADE6', 'OnGoingPeriod' => '#FDB022', 'MaturityPeriod' => '#00C7BE', 'Terminated' => '#FF2D55'];

        // Loop through each unique type and create a dataset
        // Total dataset
        $totalSubscriptionData = $subscriptions->whereIn('status', ['CoolingPeriod', 'OnGoingPeriod', 'MaturityPeriod', 'Terminated']);
        $totalDataset = [
            'label' => 'Total',
            'data' => array_map(function ($day) use ($totalSubscriptionData) {
                return $totalSubscriptionData->where('day', $day)->sum('amount');
            }, $chartData['labels']),
            'borderColor' => $backgroundColors['Terminated'], // You can adjust the color as needed
            'borderWidth' => 2,
            'pointStyle' => false,
            'fill' => true,
        ];
        $chartData['datasets'][] = $totalDataset;

        // Active dataset
        $activeSubscriptionData = $subscriptions->whereIn('status', ['CoolingPeriod', 'OnGoingPeriod']);
        $activeDataset = [
            'label' => 'Active',
            'data' => array_map(function ($day) use ($activeSubscriptionData) {
                return $activeSubscriptionData->where('day', $day)->sum('amount');
            }, $chartData['labels']),
            'borderColor' => $backgroundColors['OnGoingPeriod'], // You can adjust the color as needed
            'borderWidth' => 2,
            'pointStyle' => false,
            'fill' => true,
        ];
        $chartData['datasets'][] = $activeDataset;

        return response()->json($chartData);
    }

    public function getTotalInvestmentByMonths(Request $request)
    {
        $subscriptions = InvestmentSubscription::query()
            ->when($request->filled('year'), function ($query) use ($request) {
                $year = $request->input('year');
                $query->whereYear('created_at', $year);
            })
            ->select(
                DB::raw('MONTH(created_at) as month'), // Change DAY to MONTH
                'status',
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month', 'status') // Change 'day' to 'month'
            ->get();

        $uniqueSubscriptionsType = $subscriptions->pluck('status')->unique();

        // Initialize the chart data structure with short month names as labels
        $shortMonthNames = [];
        for ($month = 1; $month <= 12; $month++) {
            $shortMonthNames[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        $chartData = [
            'labels' => $shortMonthNames,
            'datasets' => [],
        ];

        $backgroundColors = ['CoolingPeriod' => '#32ADE6', 'OnGoingPeriod' => '#FDB022', 'MaturityPeriod' => '#00C7BE', 'Terminated' => '#FF2D55'];

        $totalSubscriptionData = $subscriptions->whereIn('status', ['CoolingPeriod', 'OnGoingPeriod', 'MaturityPeriod', 'Terminated']);
        $totalDataset = [
            'label' => 'Total',
            'data' => array_map(function ($month) use ($totalSubscriptionData) {
                $totalDataForMonth = $totalSubscriptionData->where('month', $month);

                // Check if there is data for the month
                if ($totalDataForMonth->isNotEmpty()) {
                    return $totalDataForMonth->sum('amount');
                } else {
                    return 0; // If there is no data for the month, set the sum to 0
                }
            }, range(1, 12)),
            'borderColor' => $backgroundColors['Terminated'], // You can adjust the color as needed
            'borderWidth' => 2,
            'pointStyle' => false,
            'fill' => true,
        ];
        $chartData['datasets'][] = $totalDataset;

        $activeSubscriptionData = $subscriptions->whereIn('status', ['CoolingPeriod', 'OnGoingPeriod']);
        $activeDataset = [
            'label' => 'Active',
            'data' => array_map(function ($month) use ($activeSubscriptionData) {
                $ActiveDataForMonth = $activeSubscriptionData->where('month', $month);

                // Check if there is data for the month
                if ($ActiveDataForMonth->isNotEmpty()) {
                    return $ActiveDataForMonth->sum('amount');
                } else {
                    return 0; // If there is no data for the month, set the sum to 0
                }
            }, range(1, 12)),
            'borderColor' => $backgroundColors['OnGoingPeriod'], // You can adjust the color as needed
            'borderWidth' => 2,
            'pointStyle' => false,
            'fill' => true,
        ];
        $chartData['datasets'][] = $activeDataset;

        // foreach ($uniqueSubscriptionsType as $subscription_type) {
        //     $subscription_data = $subscriptions->where('status', $subscription_type);

        //     $dataset = [
        //         'label' => $subscription_type,
        //         'data' => array_map(function ($month) use ($subscription_data) {
        //             return $subscription_data->firstWhere('month', $month)->amount ?? 0;
        //         }, range(1, 12)), // Use month numbers 1-12
        //         'borderColor' => $backgroundColors[$subscription_type],
        //         'borderWidth' => 2,
        //         'pointStyle' => false,
        //         'fill' => true
        //     ];

        //     $chartData['datasets'][] = $dataset;
        // }

        return response()->json($chartData);
    }

    public function getTotalMembers(Request $request)
    {
        $year = $request->input('year') ?? Carbon::now()->year;

        $data = [];

        // Iterate through the 12 months
        for ($i = 1; $i <= 12; $i++) {
            $totalMember = User::where('status', 1)
                ->where('setting_rank_id', 1)
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->count();

            $totalLvOne = User::where('status', 1)
                ->where('setting_rank_id', 2)
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->count();

            $totalLvTwo = User::where('status', 1)
                ->where('setting_rank_id', 3)
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->count();

            $totalLvThree = User::where('status', 1)
                ->where('setting_rank_id', 4)
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->count();

            // Store the data for this month
            $data[] = [
                'totalRankId1' => $totalMember,
                'totalRankId2' => $totalLvOne,
                'totalRankId3' => $totalLvTwo,
                'totalRankId4' => $totalLvThree,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function getPendingKyc()
    {
        $pendingMembers  = User::query()
            ->where('role', '=', 'user')
            ->where('kyc_approval', 'pending')
            ->select('id', 'name', 'email', 'kyc_approval', 'identity_number', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->paginate(5);

        // Get the media URL for 'profile_photo' for each user
        $pendingMembers->each(function ($user) {
            $user->profile_photo_url = $user->getFirstMediaUrl('profile_photo');
            $user->front_identity = $user->getFirstMediaUrl('front_identity');
            $user->back_identity = $user->getFirstMediaUrl('back_identity');
            $user->kyc_upload_date = $user->getMedia('back_identity')->first()->created_at ?? null;
        });

        return response()->json(['pendingMembers' => $pendingMembers]);
    }
}
