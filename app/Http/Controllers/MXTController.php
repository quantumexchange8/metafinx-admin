<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CoinPrice;
use App\Models\CoinPayment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use App\Models\CoinStacking;
use App\Models\SettingCoin;
use App\Models\Earning;
use App\Models\InvestmentSubscription;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CoinTransactionExport;
use App\Exports\StackingHistoryExport;
use Carbon\Carbon;
use DB;

class MXTController extends Controller
{
    //

    public function mxt_setting()
    {

        $coinTransactions = CoinPayment::all();
        $investmentPlans = InvestmentPlan::with('descriptions:id,investment_plan_id,description')
                            ->where('type', 'staking')
                            ->get();
        
        $investmentPlans->each(function ($plan) {
            $plan->plan_logo = $plan->getFirstMediaUrl('stacking_plan');
        });

        $currentDate = Carbon::today()->toDateString();
        
        $currentCoinPrice = CoinPrice::where('price_date', $currentDate)->first();
        $currentInvestor = InvestmentSubscription::whereIn('status', ['CoolingPeriod', 'OnGoingPeriod'])->distinct()->count('user_id');
        $totalSupply = SettingCoin::where('symbol', 'MXT/USD')->first();
        $totalStaking = Earning::where('category', 'staking')->sum('after_amount');
        
        return Inertia::render('MXTSetting/MXTSetting', [
            'coinTransactions' => $coinTransactions,
            'investmentPlans' => $investmentPlans,
            'currentCoinPrice' => $currentCoinPrice,
            'totalSupply' => $totalSupply,
            'totalStaking' => $totalStaking,
            'currentInvestor' => $currentInvestor,
        ]);
    }

    public function getCoinPaymentDetails(Request $request)
    {
        $coinTransactions = Transaction::with(['user:id,name,email', 'from_wallet:id,name,type', 'to_wallet:id,name,type', 'from_coin:id,address'])
                            ->whereIn('transaction_type', ['BuyCoin', 'Staking', 'SwapCoin']);
                            
        if ($request->filled('search')) {
            $search = $request->input('search');
            $coinTransactions->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                    ->orWhere('transaction_number', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $coinTransactions->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $coinTransactions->where('transaction_type', $status);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new CoinTransactionExport($coinTransactions), Carbon::now() . '-Coin_Transaction_History-report.xlsx');
        }

        $results = $coinTransactions->latest()->paginate(10);

        return response()->json($results);
    }

    public function getStackingDetails(Request $request)
    {
        $stackingHistory = CoinStacking::with(['transaction:id,transaction_number', 'user:id,name,email', 'investment_plan:id,name']);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $stackingHistory->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                        ->orWhere('subscription_number', 'like', '%' . $search . '%');
                });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $stackingHistory->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new StackingHistoryExport($stackingHistory), Carbon::now() . '-Stacking_History-report.xlsx');
        }

        $results = $stackingHistory->latest()->paginate(10);

        return response()->json($results);
    }

    public function getTotalMXTCoinByDays(Request $request)
    {
        $UnitData = Transaction::query()
                    ->when($request->filled('month'), function ($query) use ($request) {
                        $month = $request->input('month');
                        $year = $request->input('year');
                        $query->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month);
                    })
                    ->where('status', '=', 'Success')
                    ->where('category', '=', 'asset')
                    ->where('transaction_type', '=', 'BuyCoin')
                    ->select(
                        DB::raw('DAY(created_at) as day'),
                        'from_wallet_id',
                        DB::raw('SUM(unit) as unit')
                    )
                    ->groupBy('day', 'from_wallet_id')
                    ->get();

        // Get unique type to create datasets
        $uniqueDataType = $UnitData->pluck('type')->unique();

        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month;

        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        foreach ($uniqueDataType as $uniqueData) {
            $Data = $UnitData->where('type', $uniqueData);
           
            $dataset = [
                'label' => '',
                'data' => array_map(function ($day) use ($Data) {
                    return $Data->firstWhere('day', $day)->unit ?? 0;
                }, $chartData['labels']),
                'borderColor' => '#12B76A',
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        // $totalDataset = [
        //     'label' => 'Total',
        //     'data' => array_map(function ($day) use ($UnitData) {
        //         return $UnitData->where('day', $day)->sum('amount');
        //     }, $chartData['labels']),
        //     'borderColor' => '#12B76A', // You can adjust the color as needed
        //     'borderWidth' => 2,
        //     'pointStyle' => false,
        //     'fill' => true,
        // ];
        // $chartData['datasets'][] = $totalDataset;

        return response()->json($chartData);
    }

    public function getTotalMXTCoinByMonth(Request $request)
    {

        $UnitData = Transaction::query()
            ->when($request->filled('year'), function ($query) use ($request) {
                $year = $request->input('year');
                $query->whereYear('created_at', $year);
            })
            ->where('status', '=', 'Success')
            // ->where('category', '=', 'asset')
            ->where('transaction_type', '=', 'BuyCoin')
            ->select(
                DB::raw('MONTH(created_at) as month'), // Change DAY to MONTH
                'transaction_type',
                DB::raw('SUM(unit) as unit')
            )
            ->groupBy('month', 'transaction_type') // Change 'day' to 'month'
            ->get();
            
        $uniqueDataType = $UnitData->pluck('type')->unique();

        // Initialize the chart data structure with short month names as labels
        $shortMonthNames = [];
        for ($month = 1; $month <= 12; $month++) {
            $shortMonthNames[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        $chartData = [
            'labels' => $shortMonthNames,
            'datasets' => [],
        ];

        foreach ($uniqueDataType as $uniqueData) {
            $Data = $UnitData->where('type', $uniqueData);
           
            $dataset = [
                'label' => '',
                'data' => array_map(function ($month) use ($Data) {
                    return $Data->firstWhere('month', $month)->unit ?? 0;
                }, range(1, 12)),
                'borderColor' => '#12B76A',
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }
}
