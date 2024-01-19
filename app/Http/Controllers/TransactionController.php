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
use App\Exports\InternalTransferExport;
use App\Models\BalanceAdjustment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdjustmentExport;
use Illuminate\Support\Facades\App;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transaction/Transaction');
    }

    public function getPendingTransaction(Request $request, $type)
    {
        $query = Transaction::query()->with(['user:id,name,email', 'from_wallet:id,name,type', 'to_wallet:id,name,type'])
            ->where('transaction_type', $type)
            ->where('status', 'Processing');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                // $q->whereHas('wallet', function ($wallet_query) use ($search) {
                //     $wallet_query->where('name', 'like', $search);
                // })
                $q->whereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', $search)
                         ->orWhere('email', 'like', $search);
                })
                    ->orWhere('transaction_number', 'like', $search)
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
            $transactions = Transaction::whereIn('id', $request->id)->get();

            foreach ($transactions as $transaction) {
                $transaction->update([
                    'status' => 'Success'
                ]);

                if (App::environment('production')) {
                    $this->updateTransaction($transaction);
                }

                $this->updateTransaction($transaction);

                if ($transaction->type == 'Deposit') {
                    $wallet = Wallet::find($transaction->from_wallet_id);
                    $wallet->balance += $transaction->amount;
                    $wallet->save();
                }
            }
        } else {
            $transaction = Transaction::find($request->id);

            $transaction->update([
                'status' => 'Success'
            ]);

            $this->updateTransaction($transaction);

            if ($transaction->type == 'Deposit') {
                $wallet = Wallet::find($transaction->from_wallet_id);
                $wallet->balance += $transaction->amount;
                $wallet->save();
            }
        }

        return redirect()->back()->with('title', 'Approved successfully')->with('success', 'The transaction request has been approved successfully.');
    }

    public function rejectTransaction(Request $request)
    {
        $type = $request->type;

        if ($type == 'reject_selected') {
            $transactions = Transaction::whereIn('id', $request->id)->get();

            foreach ($transactions as $transaction) {

                if ($transaction->status == 'Processing') {
                    $transaction->update([
                        'status' => 'Rejected',
                        'remarks' => 'MULTIPLE Reject by admin - ID ' . $transaction->transaction_number
                    ]);

                    if (App::environment('production')) {
                        $this->updateTransaction($transaction);
                    }

                    if ($transaction->type == 'Withdrawal') {
                        $wallet = Wallet::find($transaction->from_wallet_id);

                        $wallet->balance += $transaction->amount;
                        $wallet->save();
                    }
                }
            }
        } else {
            $transaction = Transaction::find($request->id);

            $transaction->update([
                'status' => 'Rejected',
                'remarks'=> $request->remark
            ]);

            if (App::environment('production')) {
                $this->updateTransaction($transaction);
            }

            if ($transaction->type == 'Withdrawal') {
                $wallet = Wallet::find($transaction->from_wallet_id);

                $wallet->balance += $transaction->amount;
                $wallet->save();
            }
        }

        return redirect()->back()->with('title', 'Rejected successfully')->with('success', 'The transaction request has been rejected successfully.');
    }

    public function getTransactionHistory(Request $request, $type)
    {
        $query = Transaction::query()->with(['user:id,name,email', 'from_wallet:id,name,type', 'to_wallet:id,name,type'])
            ->where('transaction_type', $type)
            ->whereNotIn('status', ['Processing', 'Pending']);

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                // $q->whereHas('from_wallet', function ($wallet_query) use ($search) {
                //     $wallet_query->where('name', 'like', $search);
                    
                // })
                // ->orWhereHas('to_wallet', function ($wallet_query) use ($search) {
                //     $wallet_query->where('name', 'like', $search);
                // })
                $q->WhereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', $search)
                         ->orWhere('email', 'like', $search);
                })
                    ->orWhere('transaction_number', 'like', $search)
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
        // $search = $request->filled('search') ? '%' . $request->input('search') . '%' : null;
        // $dateRange = $request->filled('date') ? explode(' - ', $request->input('date')) : null;

        $query = Transaction::query()->with(
            'user:id,name,email', 
            'from_wallet:id,name,type', 'to_wallet:id,name,type',
            'from_coin:id,setting_coin_id', 'to_coin:id,setting_coin_id'
            )
                ->whereIn('transaction_type', [$type, 'AssetAdjustment']);
                // ->orWhere('transaction_type', 'AssetAdjustment');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                })
                // ->orWhere('new_amount', 'like', $search)
                //     ->orWhere('amount', 'like', $search)
                //     ->orWhere('description', 'like', $search);
                ->orWhere('transaction_number', 'like', $search)
                ->orWhere('transaction_amount', 'like', $search);
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
            if ($type === 'WalletAdjustment' || $type === 'AssettAdjustment') {
                return Excel::download(new AdjustmentExport($query,$type), Carbon::now() . '-' . 'Asset_and_Balance_History-report.xlsx');
            } elseif ($type == 'InternalTransfer') {
                return Excel::download(new InternalTransferExport($query), Carbon::now() . '-' . $type . '_History-report.xlsx');
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
        $hashedToken = md5($rec->transaction_number . $rec->to_wallet_address);
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
