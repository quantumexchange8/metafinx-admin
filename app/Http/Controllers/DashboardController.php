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
            'newMemberCount' => $newMemberCount,
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'totalInvestment' => InvestmentSubscription::all()->sum('amount'),
            'totalMembers' => User::where('status', 1)->count(),
            'pendingMemberCount' => $pendingMemberCount,
            'pendingTransactions' => $pendingTransactions,
            'pendingTransactionCount' => $pendingTransactionCount,
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

    public function getPendingKyc()
    {
        $pendingMembers  = User::query()
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
