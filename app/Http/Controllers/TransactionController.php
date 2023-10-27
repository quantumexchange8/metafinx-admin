<?php

namespace App\Http\Controllers;

use App\Exports\DepositExport;
use App\Exports\WithdrawalExport;
use App\Models\Payment;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

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

                if ($payment->type == 'Deposit') {
                    $wallet = Wallet::where('user_id', $payment->id)->first();
                    $wallet->balance += $payment->amount;
                    $wallet->save();
                }
            }
        } else {
            $payment = Payment::find($request->id);

            $payment->update([
                'status' => 'Success'
            ]);

            if ($payment->type == 'Deposit') {
                $wallet = Wallet::where('user_id', $payment->id)->first();
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
                        'status' => 'Rejected'
                    ]);

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
                'status' => 'Rejected'
            ]);

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

        if ($request->has('exportStatus')) {
            if ($type == 'Deposit') {
                return Excel::download(new DepositExport($query), Carbon::now() . '-' . $type . '_History-report.xlsx');
            } elseif ($type == 'Withdrawal') {
                return Excel::download(new WithdrawalExport($query), Carbon::now() . '-' . $type . '_History-report.xlsx');
            }
        }

        $results = $query->latest()->paginate(10);

        $results->each(function ($user_deposit) {
            $user_deposit->user->profile_photo_url = $user_deposit->user->getFirstMediaUrl('profile_photo');
        });


        return response()->json([$type => $results]);
    }
}
