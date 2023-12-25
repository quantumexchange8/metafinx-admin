<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentPlanRequest;
use App\Models\InvestmentPlan;
use App\Models\InvestmentPlanDescription;
use App\Models\InvestmentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IpoSchemeController extends Controller
{
    public function setting()
    {
        $investmentPlans = InvestmentPlan::with('descriptions:id,investment_plan_id,description')->get();

        return Inertia::render('IpoSchemeSetting/IpoSchemeSetting', [
            'investmentPlans' => $investmentPlans
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

    public function getPendingSubscription(Request $request)
    {
        $selectedPlans = InvestmentSubscription::with(['investment_plan:id,name,type', 'user:id,name,email'])
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
            ->whereHas('investment_plan', function ($planQuery) {
                $planQuery->where('type', 'ebmi');
            })
            ->whereNot('document_status', 'approve')
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

    public function approveEbmi(Request $request)
    {
        $subscription = InvestmentSubscription::find($request->id);

        $subscription->update([
            'document_status' => 'approve',
            'document_approval_date' => Carbon::now()
        ]);

        return redirect()->back()->with('title', 'EBMI approved')->with('success', 'This EBMI plan has been approved successfully.');
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

    public function addInvestmentPlan(InvestmentPlanRequest $request)
    {
        $investmentPlan = InvestmentPlan::create([
            'name' => $request->plan_name,
            'investment_min_amount' => $request->investment_min_amount,
            'roi_per_annum' => $request->roi_per_annum,
            'investment_period' => $request->investment_period,
        ]);

        $descriptionItems = $request->descriptions;

        foreach ($descriptionItems as $descriptionItem) {
            InvestmentPlanDescription::create([
                'investment_plan_id' => $investmentPlan->id,
                'description' => $descriptionItem
            ]);
        }

        return redirect()->back()->with('title', 'Investment plan added')->with('success', 'This new investment plan has been added successfully.');
    }

    public function editInvestmentPlan(InvestmentPlanRequest $request)
    {
        $investmentPlan = InvestmentPlan::find($request->id);

        $investmentPlan->update([
            'name' => $request->plan_name,
            'investment_min_amount' => $request->investment_min_amount,
            'roi_per_annum' => $request->roi_per_annum,
            'investment_period' => $request->investment_period,
        ]);

        $descriptionItems = $request->descriptions;

        foreach ($investmentPlan->descriptions as $item) {
            $item->delete();
        }

        foreach ($descriptionItems as $descriptionItem) {
            InvestmentPlanDescription::create([
                'investment_plan_id' => $investmentPlan->id,
                'description' => $descriptionItem
            ]);
        }

        return redirect()->back()->with('title', 'Investment plan updated')->with('success', 'This investment plan has been updated successfully.');
    }
}
