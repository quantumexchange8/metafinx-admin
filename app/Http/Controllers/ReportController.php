<?php

namespace App\Http\Controllers;

use App\Exports\EarningReportExport;
use App\Http\Requests\InvestmentPlanRequest;
use App\Models\InvestmentPlan;
use App\Models\InvestmentPlanDescription;
use App\Models\InvestmentSubscription;
use App\Models\User;
use App\Models\Earning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {

        $totatMonthlyReturn = Earning::where('type', 'StandardRewards')->sum('after_amount');
        $totalReferralEarning = Earning::where('type', 'ReferralEarnings')->where('category', 'standard')->sum('after_amount');
        $totatAffiliateEarning = Earning::where('type', 'AffiliateEarnings')->sum('after_amount');
        $totatDividendEarning = Earning::where('type', 'DividendEarnings')->sum('after_amount');
        $totatAffiliateDividendEarning = Earning::where('type', 'AffiliateDividendEarnings')->sum('after_amount');
        $totatStakingReward = Earning::where('type', 'StakingRewards')->sum('after_amount');
        $totatReferralStaking = Earning::where('type', 'ReferralEarnings')->where('category', 'staking')->sum('after_amount');
        $totatPairingEarning = Earning::where('type', 'PairingEarnings')->sum('after_amount');

        return Inertia::render('Report/Report', [
            'totatMonthlyReturn' => $totatMonthlyReturn,
            'totalReferralEarning' => $totalReferralEarning,
            'totatAffiliateEarning' => $totatAffiliateEarning ,
            'totatDividendEarning' => $totatDividendEarning ? $totatDividendEarning : "0.00",
            'totatAffiliateDividendEarning' => $totatAffiliateDividendEarning ? $totatAffiliateDividendEarning : "0.00",
            'totatStakingReward' => $totatStakingReward ? $totatStakingReward : "0.00",
            'totatReferralStaking' => $totatReferralStaking,
            'totatPairingEarning' => $totatPairingEarning,
        ]);
    }

    public function getPayoutDetails(Request $request)
    {

        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name,email'])
            ->where('type', 'ReferralEarnings')
            ->where('category', 'standard');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('downline', function ($downline) use ($search) {
                    $downline->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhereHas('user', function ($upline) use ($search){
                    $upline->where('name', 'like', $search);
                });
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $referralEarning = $query->where('type', 'ReferralEarnings')->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('after_amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
            $user_deposit->downline->profile_photo_url = $user_deposit->downline->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            'referralEarning' => $referralEarning,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function getTotalPayoutByDays(Request $request, $type, $category)
    {
        $payouts = Earning::query()->with(['downline:id,name,email', 'user:id,name'])
            ->when($request->filled('month'), function ($q) use ($request) {
                $month = $request->input('month');
                $q->whereMonth('created_at', $month);
            })
            ->select(
                DB::raw('DAY(created_at) as day'),
                'type',
                DB::raw('SUM(after_amount) as amount')
            )
            ->groupBy('day', 'type')
            ->where('type', $type)
            ->where('category', $category)
            ->get();

        // Get unique type to create datasets
        $uniquePayoutType = $payouts->pluck('type')->unique();

        $year = Carbon::now()->year;
        $month = $request->month;
        // Initialize the chart data structure
        $chartData = [
            'labels' => range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)), // Generate an array of days
            'datasets' => [],
        ];

        $backgroundColors = [
            'ReferralEarnings' => '#00C7BE',
            'StandardRewards' => '#FF2D55',
            'AffiliateEarnings' => '#AF52DE',
            'DividendEarnings' => '#5856D6',
            'AffiliateDividendEarnings' => '#5856D6',
            'StakingRewards' => '#5856D6',
            'PairingEarnings' => '#5856D6',
        ];

        // Loop through each unique type and create a dataset
        foreach ($uniquePayoutType as $payoutType) {
            $payoutData = $payouts->where('type', $payoutType);

            $dataset = [
                'label' => $payoutData->first()->type,
                'data' => array_map(function ($day) use ($payoutData) {
                    return $payoutData->firstWhere('day', $day)->amount ?? 0;
                }, $chartData['labels']),
                'borderColor' => $backgroundColors[$payoutType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getTotalPayoutByMonths(Request $request, $type, $category)
    {
        $payouts = Earning::query()->with(['downline:id,name,email', 'user:id,name'])
            ->when($request->filled('year'), function ($q) use ($request) {
                $year = $request->input('year');
                $q->whereYear('created_at', $year);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . $request->input('search') . '%';
                $q->where(function ($q2) use ($search) {
                    $q2->whereHas('downline', function ($downline) use ($search) {
                        $downline->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    })
                    ->orWhereHas('user', function ($upline) use ($search){
                        $upline->where('name', 'like', $search);
                    });
                });
            })
            ->when($request->filled('date'), function ($q) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

                $q->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->select(
                DB::raw('Month(created_at) as month'),
                'type',
                DB::raw('SUM(after_amount) as amount')
            )
            ->where('type', $type)
            ->where('category', $category)
            ->groupBy('month', 'type')
            ->get();

        $uniquePayoutType = $payouts->pluck('type')->unique();

        // Initialize the chart data structure with short month names as labels
        $shortMonthNames = [];
        for ($month = 1; $month <= 12; $month++) {
            $shortMonthNames[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        $chartData = [
            'labels' => $shortMonthNames,
            'datasets' => [],
        ];

        $backgroundColors = [
            'ReferralEarnings' => '#00C7BE',
            'StandardRewards' => '#FF2D55',
            'AffiliateEarnings' => '#AF52DE',
            'DividendEarnings' => '#5856D6',
            'AffiliateDividendEarnings' => '#5856D6',
            'StakingRewards' => '#5856D6',
            'BinaryReferralEarnings' => '#5856D6',
            'PairingEarnings' => '#5856D6',
        ];

        foreach ($uniquePayoutType as $payoutType) {
            $payoutData = $payouts->where('type', $payoutType);

            $dataset = [
                'label' => $payoutData->first()->type,
                'data' => array_map(function ($month) use ($payoutData) {
                    return $payoutData->firstWhere('month', $month)->amount ?? 0;
                }, range(1, 12)), // Use month numbers 1-12
                'borderColor' => $backgroundColors[$payoutType],
                'borderWidth' => 2,
                'pointStyle' => false,
                'fill' => true
            ];

            $chartData['datasets'][] = $dataset;
        }

        return response()->json($chartData);
    }

    public function getMonthlyReturnPayoutDetails(Request $request)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name,email'])
            ->where('type', 'StandardRewards');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('downline', function ($downline) use ($search) {
                    $downline->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhereHas('user', function ($upline) use ($search){
                    $upline->where('name', 'like', $search);
                });
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $monthlyReturn = $query->where('type', 'StandardRewards')->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('after_amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            'monthlyReturn' => $monthlyReturn,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function getAffiliateEarningPayoutDetails(Request $request)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name,email'])
            ->where('type', 'AffiliateEarnings');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('downline', function ($downline) use ($search) {
                    $downline->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhereHas('user', function ($upline) use ($search){
                    $upline->where('name', 'like', $search);
                });
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $affiliateEarning = $query->where('type', 'AffiliateEarnings')->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('after_amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            'totalAmount' => $totalAmount,
            'affiliateEarning' => $affiliateEarning,
        ]);
    }

    public function getDividendEarningPayoutDetails(Request $request)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name,email'])
            ->where('type', 'AffiliateEarnings');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('downline', function ($downline) use ($search) {
                    $downline->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhereHas('user', function ($upline) use ($search){
                    $upline->where('name', 'like', $search);
                });
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $dividendEarning = $query->where('type', 'DividendEarnings')->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('after_amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            'totalAmount' => $totalAmount,
            'dividendEarning' => $dividendEarning,
        ]);
    }

    public function getEarningPayoutDetails(Request $request, $type, $category)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name,email'])
            ->where('type', $type)
            ->where('category', $category);

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('downline', function ($downline) use ($search) {
                    $downline->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhereHas('user', function ($upline) use ($search){
                    $upline->where('name', 'like', $search);
                });
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $totalEarning = $query->where('type', $type)->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('after_amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            'totalAmount' => $totalEarning,
            'totalEarning' => $totalEarning,
        ]);
    }

}
