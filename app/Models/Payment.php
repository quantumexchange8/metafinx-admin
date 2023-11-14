<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_id',
        'txn_hash',
        'amount',
        'price',
        'payment_charges',
        'to_wallet_address',
        'type',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $payment = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('payment')
            ->logOnly(['id', 'user_id', 'wallet_id', 'transaction_id', 'type', 'amount', 'price', 'status'])
            ->setDescriptionForEvent(function (string $eventName) use ($payment) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} payment_id of {$payment->transaction_id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }
}
