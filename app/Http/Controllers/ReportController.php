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
        return Inertia::render('Report/Report');
    }

    public function getPayoutDetails(Request $request)
    {
        $query = Earning::query()
            ->with(['downline:id,name,email', 'upline:id,name']);

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

        // $monthlyReturn = $query->where('type', 'Monthly Return')->distinct('investment_subscription_id')->sum('after_amount');
        // $quarterlyDividend = $query->where('type', 'Quarterly Divdend')->distinct('investment_subscription_id')->sum('after_amount');
        $referralEarning = $query->where('type', 'Referral Earning')->distinct('investment_subscription_id')->sum('after_amount');
        // $affiliateEarning = $query->where('type', 'Affiliate Earning')->distinct('investment_subscription_id')->sum('after_amount');
        // $dividendEarning = $query->where('type', 'Dividend Earning')->distinct('investment_subscription_id')->sum('after_amount');
        // $ticketBonus = $query->where('type', 'Ticket Bonus')->distinct('investment_subscription_id')->sum('after_amount');

        if ($request->filled('type')) {
            $type = $request->input('type');
            $query->where('type', $type);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new EarningReportExport($query), Carbon::now() . '-' . $type . '-report.xlsx');
        }

        $results = $query->latest()->paginate(10);

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([
            'results' => $results,
            // 'monthlyReturn' => $monthlyReturn,
            // 'quarterlyDividend' => $quarterlyDividend,
            'referralEarning' => $referralEarning,
            // 'affiliateEarning' => $affiliateEarning,
            // 'dividendEarning' => $dividendEarning,
            // 'ticketBonus' => $ticketBonus,
            // 'selectedPayout' => $selectedPayout,
        ]);
    }

}
