<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPlan;
use App\Models\InvestmentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IpoSchemeController extends Controller
{
    public function setting()
    {
        return Inertia::render('IpoSchemeSetting/IpoSchemeSetting', [
            'investmentPlans' => InvestmentPlan::all(),
        ]);
    }

    public function getSubscriptionDetails(Request $request)
    {
        $selectedPlans = InvestmentSubscription::with(['investment_plan:id,name', 'user:id,name,email'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                        ->orWhere('subscription_id', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->latest()
            ->paginate(10);

        $selectedPlans->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json($selectedPlans);
    }

    public function getSelectedPlans(Request $request)
    {
        $selectedPlans = InvestmentSubscription::with('investment_plan')
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->select('investment_plan_id', DB::raw('count(*) as plan_count'))
            ->groupBy('investment_plan_id')
            ->get();

        $labels = $selectedPlans->pluck('investment_plan.name');
        $datasetData = $selectedPlans->pluck('plan_count');

        return response()->json([
            'labels' => $labels,
            'datasetData' => $datasetData
        ]);
    }

    public function updateStatus(Request $request)
    {
        $investment_plan = InvestmentPlan::find($request->id);

        if ($investment_plan->status == 'active')
        {
            $investment_plan->update([
                'status' => 'inactive'
            ]);
        } elseif ($investment_plan->status == 'inactive') {
            $investment_plan->update([
                'status' => 'active'
            ]);
        }

        return redirect()->back();
    }
}
