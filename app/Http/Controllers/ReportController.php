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

        $totatMonthlyReturn = Earning::where('type', 'monthly_return')->sum('after_amount');
        $totalReferralEarning = Earning::where('type', 'referral_earnings')->sum('after_amount');
        
        return Inertia::render('Report/Report', [
            'totatMonthlyReturn' => $totatMonthlyReturn,
            'totalReferralEarning' => $totalReferralEarning,
        ]);
    }

    public function getPayoutDetails(Request $request)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'user:id,name']);

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

        // $monthlyReturn = $query->where('type', 'monthly_return')->sum('after_amount');
        // $quarterlyDividend = $query->where('type', 'Quarterly Divdend'))->sum('after_amount');
        $referralEarning = $query->where('type', 'referral_earnings')->sum('after_amount');
        // $affiliateEarning = $query->where('type', 'Affiliate Earning')->sum('after_amount');
        // $dividendEarning = $query->where('type', 'Dividend Earning')->sum('after_amount');
        // $ticketBonus = $query->where('type', 'Ticket Bonus')->sum('after_amount');

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
            // 'monthlyReturn' => $monthlyReturn,
            // 'quarterlyDividend' => $quarterlyDividend,
            'referralEarning' => $referralEarning,
            'totalAmount' => $totalAmount,
            // 'affiliateEarning' => $affiliateEarning,
            // 'dividendEarning' => $dividendEarning,
            // 'ticketBonus' => $ticketBonus,
            // 'selectedPayout' => $selectedPayout,
        ]);
    }

    public function getTotalPayoutByDays(Request $request)
    {
        $payouts = Earning::query()->with(['downline:id,name,email', 'user:id,name'])
            ->when($request->filled('month'), function ($q) use ($request) {
                $month = $request->input('month');
                $q->whereMonth('created_at', $month);
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
                DB::raw('DAY(created_at) as day'),
                'type',
                DB::raw('SUM(after_amount) as amount')
            )
            ->groupBy('day', 'type')
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

        $backgroundColors = ['referral_earnings' => '#00C7BE', 'monthly_return' => '#FF2D55', 'affiliate_earnings' => '#AF52DE', 'dividend_earning' => '#5856D6'];

        // $backgroundColors = ['Monthly Return' => '#FF2D55', 'Quarterly Dividend' => '#FDB022', 'referral_earnings' => '#00C7BE', 
        // 'Affiliate Earning' => '#AF52DE', 'Dividend Earning' => '#5856D6', 'Ticket Bonus' => '#32ADE6'];

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

    public function getTotalPayoutByMonths(Request $request)
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

        $backgroundColors = ['referral_earnings' => '#00C7BE', 'monthly_return' => '#FF2D55'];

        // $backgroundColors = ['Monthly Return' => '#FF2D55', 'Quarterly Dividend' => '#FDB022', 'referral_earnings' => '#00C7BE', 
        // 'Affiliate Earning' => '#AF52DE', 'Dividend Earning' => '#5856D6', 'Ticket Bonus' => '#32ADE6'];

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
            ->with(['downline:id,name,email', 'user:id,name']);

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

        $monthlyReturn = $query->where('type', 'monthly_return')->sum('after_amount');
        // $quarterlyDividend = $query->where('type', 'Quarterly Divdend'))->sum('after_amount');
        // $referralEarning = $query->where('type', 'referral_earnings')->sum('after_amount');
        // $affiliateEarning = $query->where('type', 'Affiliate Earning')->sum('after_amount');
        // $dividendEarning = $query->where('type', 'Dividend Earning')->sum('after_amount');
        // $ticketBonus = $query->where('type', 'Ticket Bonus')->sum('after_amount');

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
            // 'quarterlyDividend' => $quarterlyDividend,
            // 'referralEarning' => $referralEarning,
            // 'affiliateEarning' => $affiliateEarning,
            // 'dividendEarning' => $dividendEarning,
            // 'ticketBonus' => $ticketBonus,
            // 'selectedPayout' => $selectedPayout,
        ]);
    }

}
