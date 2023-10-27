<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $payment = Payment::where('status', 'Success')->get();
        $totalDeposit = $payment->where('type', 'Deposit')->sum('amount');
        $totalWithdrawal = $payment->where('type', 'Withdrawal')->sum('amount');

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

        return Inertia::render('Dashboard', [
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'totalInvestment' => InvestmentSubscription::all()->sum('amount'),
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
                $query->whereMonth('created_at', $month);
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

        $year = Carbon::now()->year;
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
