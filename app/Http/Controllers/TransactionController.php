<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Exports\DepositExport;
use App\Models\CoinAdjustment;
use App\Models\AssetAdjustment;
use App\Exports\WithdrawalExport;
use App\Models\BalanceAdjustment;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdjustmentExport;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transaction/Transaction');
    }

    public function getPendingTransaction(Request $request, $type)
    {
        $query = Payment::query()->with(['user', 'wallet'])
            ->where('type', $type)
            ->where('status', 'Processing');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('wallet', function ($wallet_query) use ($search) {
                    $wallet_query->where('name', 'like', $search);
                })
                    ->orWhere('transaction_id', 'like', $search)
                    ->orWhere('amount', 'like', $search);
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->has('exportStatus')) {
            if ($type == 'Deposit') {
                return Excel::download(new DepositExport($query), Carbon::now() . '-Pending_' . $type . '-report.xlsx');
            } elseif ($type == 'Withdrawal') {
                return Excel::download(new WithdrawalExport($query), Carbon::now() . '-Pending_' . $type . '-report.xlsx');
            }
        }

        $results = $query->latest()->paginate(10);

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });


        return response()->json([$type => $results]);
    }

    public function approveTransaction(Request $request)
    {
        $type = $request->type;

        if ($type == 'approve_selected') {
            $payments = Payment::whereIn('id', $request->id)->get();

            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'Success'
                ]);

                $this->updateTransaction($payment);

                if ($payment->type == 'Deposit') {
                    $wallet = Wallet::find($payment->wallet_id);
                    $wallet->balance += $payment->amount;
                    $wallet->save();
                }
            }
        } else {
            $payment = Payment::find($request->id);

            $payment->update([
                'status' => 'Success'
            ]);

            $this->updateTransaction($payment);

            if ($payment->type == 'Deposit') {
                $wallet = Wallet::find($payment->wallet_id);
                $wallet->balance += $payment->amount;
                $wallet->save();
            }
        }

        return redirect()->back()->with('title', 'Approved successfully')->with('success', 'The transaction request has been approved successfully.');
    }

    public function rejectTransaction(Request $request)
    {
        $type = $request->type;

        if ($type == 'reject_selected') {
            $payments = Payment::whereIn('id', $request->id)->get();

            foreach ($payments as $payment) {

                if ($payment->status == 'Processing') {
                    $payment->update([
                        'status' => 'Rejected',
                        'remarks' => 'MULTIPLE Reject - ID ' . $payment->transaction_id
                    ]);

                    $this->updateTransaction($payment);

                    if ($payment->type == 'Withdrawal') {
                        $wallet = Wallet::find($payment->wallet_id);

                        $wallet->balance += $payment->amount;
                        $wallet->save();
                    }
                }
            }
        } else {
            $payment = Payment::find($request->id);

            $payment->update([
                'status' => 'Rejected',
                'remarks'=> $request->remark
            ]);

            $this->updateTransaction($payment);

            if ($payment->type == 'Withdrawal') {
                $wallet = Wallet::find($payment->wallet_id);

                $wallet->balance += $payment->amount;
                $wallet->save();
            }
        }

        return redirect()->back()->with('title', 'Rejected successfully')->with('success', 'The transaction request has been rejected successfully.');
    }

    public function getTransactionHistory(Request $request, $type)
    {
        $query = Payment::query()->with(['user', 'wallet'])
            ->where('type', $type)
            ->whereNotIn('status', ['Processing', 'Pending']);

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('wallet', function ($wallet_query) use ($search) {
                    $wallet_query->where('name', 'like', $search);
                })
                    ->orWhere('transaction_id', 'like', $search)
                    ->orWhere('amount', 'like', $search);
            });
        }

        if ($request->filled('date')) {
            $date = $request->input('date');
            $dateRange = explode(' - ', $date);
            $start_date = Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        if ($request->filled('filter')) {
            $filter = $request->input('filter') ;
            $query->where(function ($q) use ($filter) {
                $q->where('status', $filter);
            });
        }

        if ($request->has('exportStatus')) {
            if ($type == 'Deposit') {
                return Excel::download(new DepositExport($query), Carbon::now() . '-' . $type . '_History-report.xlsx');
            } elseif ($type == 'Withdrawal') {
                return Excel::download(new WithdrawalExport($query), Carbon::now() . '-' . $type . '_History-report.xlsx');
            }
        }

        $results = $query->latest()->paginate(10);

        $totalAmount = $query->sum('amount');

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });


        return response()->json([
            $type => $results,
            'totalAmount' => $totalAmount
        ]);
    }

    public function getBalanceHistory(Request $request, $type)
    {
        $search = $request->filled('search') ? '%' . $request->input('search') . '%' : null;
        $dateRange = $request->filled('date') ? explode(' - ', $request->input('date')) : null;

        $query = AssetAdjustment::query()->with('user:id,name,email', 'wallet:id,name,type', 'setting_coin:id,name');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', $search);
                })->orWhere('new_amount', 'like', $search)
                    ->orWhere('amount', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if ($dateRange) {
            $query->whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateRange[0])->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $dateRange[1])->endOfDay()
            ]);
        }

        if ($request->has('exportStatus')) {
            if ($type == 'WalletAdjustment' || 'AssettAdjustment') {
                return Excel::download(new AdjustmentExport($query,$type), Carbon::now() . '-' . 'Asset_and_Balance_History-report.xlsx');
            } 
        }

        $results = $query->latest()->paginate(10);

        $results->each(function ($user_deposit) use ($type) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });

        return response()->json([$type => $results]);
    }

    
    private function updateTransaction($rec)
    {
        $hashedToken = md5($rec->transaction_id . $rec->to_wallet_address);
        $params = [
            "token" => $hashedToken,
            "transactionID" => $rec->transaction_id,
            "address" => $rec->to_wallet_address,
            "amount" => $rec->amount,
            "status" => $rec->status == 'Success' ? 2 : 1,
            "remarks" => $rec->remarks
        ];

        $url = 'https://thundertrade.currenttech.pro/updateTransaction';
        $response = Http::post($url, $params);
    }

}
