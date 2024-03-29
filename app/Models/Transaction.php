<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'category',
        'transaction_type',
        'from_wallet_id',
        'to_wallet_id',
        'from_coin_id',
        'to_coin_id',
        'transaction_number',
        'to_wallet_address',
        'txn_hash',
        'amount',
        'transaction_charges',
        'transaction_amount',
        'new_wallet_amount',
        'new_coin_amount',
        'unit',
        'price_per_unit',
        'status',
        'remarks',
        'handle_by',
    ];

    public function from_wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id', 'id');
    }

    public function to_wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id', 'id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function from_coin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coin::class, 'from_coin_id', 'id');
    }

    public function to_coin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Coin::class, 'to_coin_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $transaction = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('transaction')
            ->logOnly(['id', 'user_id', 'category', 'transaction_type', 'from_wallet_id', 'to_wallet_id', 'from_coin_id', 'to_coin_id', 'transaction_number', 'to_wallet_address', 'txn_hash', 'amount', 'transaction_charges', 'transaction_amount', 'new_wallet_amount', 'new_coin_amount', 'unit', 'price_per_unit', 'status', 'remarks', 'handle_by'])
            ->setDescriptionForEvent(function (string $eventName) use ($transaction) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} transaction with ID: {$transaction->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
