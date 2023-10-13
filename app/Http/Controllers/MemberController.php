<?php

namespace App\Http\Controllers;

use App\Models\SettingRank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function listing()
    {
        $settingRanks = SettingRank::select('id', 'name')->get();
        return Inertia::render('Member/MemberListing', [
            'settingRanks' => $settingRanks
        ]);
    }

    public function getMemberDetails(Request $request, $settingRankId)
    {
        $members = User::query()
            ->where('setting_rank_id', $settingRankId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $date = $request->input('date');
                $dateRange = explode(' - ', $date);
                $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->with(['media'])
            ->withSum('wallets', 'balance')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $members->getCollection()->transform(function ($member) {
            $member->total_children = count($member->getChildrenIds());
            return $member;
        });

        return response()->json($members);
    }
}
