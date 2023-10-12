<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;

        $newMemberCount = User::query()
            ->where('status', 1)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $payment = Payment::where('status', 'Success')->get();
        $totalDeposit = $payment->where('type', 'Deposit')->sum('amount');
        $totalWithdrawal = $payment->where('type', 'Withdrawal')->sum('amount');

        $pendingDeposits = Payment::query()
            ->where('status', 'Processing')
            ->where('type', 'Deposit')
            ->with(['user:id,name,email'])
            ->select('id', 'user_id', 'status', 'amount')
            ->limit(5)
            ->get();

        // Get the media URL for 'profile_photo' for each user
        $pendingDeposits->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        $pendingWithdrawals  = Payment::query()
            ->where('status', 'Pending')
            ->where('type', 'Withdrawal')
            ->with(['user:id,name,email'])
            ->select('id', 'user_id', 'status', 'amount')
            ->limit(5)
            ->get();

        // Get the media URL for 'profile_photo' for each user
        $pendingWithdrawals->each(function ($user_withdrawal) {
            $user_withdrawal->user->profile_photo_url = $user_withdrawal->user->getFirstMediaUrl('profile_photo');
        });


        return Inertia::render('Dashboard', [
            'newMemberCount' => $newMemberCount,
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'totalInvestment' => InvestmentSubscription::all()->sum('amount'),
            'totalMembers' => User::where('status', 1)->count(),
            'pendingDeposits' => $pendingDeposits,
            'pendingWithdrawals' => $pendingWithdrawals,
        ]);
    }

    public function getTotalMembers(Request $request)
    {
        $year = $request->input('year') ?? 2023;

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
}
