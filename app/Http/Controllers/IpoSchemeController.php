<?php

namespace App\Http\Controllers;

use App\Exports\SubscriptionExport;
use App\Http\Requests\InvestmentPlanRequest;
use App\Models\InvestmentPlan;
use App\Models\InvestmentPlanDescription;
use App\Models\InvestmentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class IpoSchemeController extends Controller
{
    public function setting()
    {
        $investmentPlans = InvestmentPlan::with('descriptions:id,investment_plan_id,description')
                            ->where('type', 'standard')
                            ->get();
        $totalInvestmentCount = InvestmentSubscription::sum('amount');
        $totalEarningCount = InvestmentSubscription::sum('total_earning');
        $onGoingAmountCount = InvestmentSubscription::where('status', 'OnGoingPeriod')->sum('amount');

        $groupedInvestmentPlans = $investmentPlans->groupBy('type');

        $investmentPlansUrl = $groupedInvestmentPlans->map(function ($group, $type) {
            // Select the fields you want for each group
            return [
                'type' => $type,
                'plans' => $group->map(function ($investmentPlan) {
                    return [
                        'id' => $investmentPlan->id,
                        'name' => $investmentPlan->getTranslation('name', app()->getLocale()),
                        'roi_per_annum' => $investmentPlan->roi_per_annum,
                        'commision_multiplier' => $investmentPlan->commision_multiplier,
                        'descriptions' => $investmentPlan->descriptions->map(function ($description) {
                            return [
                                'description' => $description->getTranslation('description', app()->getLocale()),
                            ];
                        }),
                        'type' => $investmentPlan->type,
                        'plan_logo' => $investmentPlan->getFirstMediaUrl('standard_plan')
                    ];
                }),
            ];
        });

        $investmentPlans->each(function ($plan) {
            $plan->plan_logo = $plan->getFirstMediaUrl('standard_plan');
        });
        
        return Inertia::render('IpoSchemeSetting/IpoSchemeSetting', [
            'investmentPlans' => $investmentPlans,
            'investmentPlansUrl' => $investmentPlansUrl,
            'totalInvestmentCount' => $totalInvestmentCount,
            'totalEarningCount' => $totalEarningCount,
            'onGoingAmountCount' => $onGoingAmountCount,
        ]);
    }

    public function getSubscriptionDetails(Request $request)
    {
        $selectedPlans = InvestmentSubscription::with(['investment_plan:id,name,type', 'user:id,name,email']);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $selectedPlans->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                    ->orWhere('subscription_id', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $selectedPlans->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $selectedPlans->where('status', $status);
        }

        if ($request->has('exportStatus')) {
            return Excel::download(new SubscriptionExport($selectedPlans), Carbon::now() . '-Subscription_History-report.xlsx');
        }

        $results = $selectedPlans->latest()->paginate(10);

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json($results);
    }

    public function getPendingSubscription(Request $request)
    {
        $selectedPlans = InvestmentSubscription::with(['investment_plan:id,name,type', 'user:id,name,email'])
            ->whereHas('investment_plan', function ($query) {
                $query->where('type', 'ebmi');
            })
            ->whereNot('document_status', 'approve');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $selectedPlans->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                    ->orWhere('subscription_id', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
            $selectedPlans->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $selectedPlans->where('status', $status);
        }    

        if ($request->has('exportStatus')) {
            return Excel::download(new SubscriptionExport($selectedPlans), Carbon::now() . '-Pending_Subscription-report.xlsx');
        }

        $results = $selectedPlans->latest()->paginate(10);

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json($results);
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
            'type' => $request->plan_type,
        ]);

        $descriptionItems = $request->descriptions;

        foreach ($descriptionItems as $descriptionItem) {
            InvestmentPlanDescription::create([
                'investment_plan_id' => $investmentPlan->id,
                'description' => $descriptionItem
            ]);
        }

        if ($request->hasfile('plan_logo')){
            if ($request->plan_type == 'standard') {
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('standard_plan');
            } elseif ($request->plan_type == 'ebmi') {
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('ebmi_plan');
            } elseif ($request->plan_type == 'stacking') {
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('stacking_plan');
            }
        }

        return redirect()->back()->with('title', 'Investment plan added')->with('success', 'This new investment plan has been added successfully.');
    }

    public function editInvestmentPlan(Request $request)
    {
        $investmentPlan = InvestmentPlan::find($request->id);

        $investmentPlan->update([
            'name' => $request->plan_name,
            'investment_min_amount' => $request->investment_min_amount,
            'roi_per_annum' => $request->roi_per_annum,
            'investment_period' => $request->investment_period,
            'commision_multiplier' => $request->commision_multiplier,
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
        
        if ($request->hasfile('plan_logo')){
            if ($investmentPlan->type == 'standard') {
                $investmentPlan->clearMediaCollection('standard_plan');
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('standard_plan');
            } elseif ($investmentPlan->type == 'ebmi') {
                $investmentPlan->clearMediaCollection('ebmi_plan');
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('ebmi_plan');
            } elseif ($investmentPlan->type == 'staking') {
                $investmentPlan->clearMediaCollection('stacking_plan');
                $investmentPlan->addMedia($request->plan_logo)->toMediaCollection('stacking_plan');
            }
        }
        return redirect()->back()->with('title', 'Investment plan updated')->with('success', 'This investment plan has been updated successfully.');
    }

    
}
