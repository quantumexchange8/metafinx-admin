<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Wallet extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'name',
        'balance',
        'token',
        'address',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $wallet = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('wallet')
            ->logOnly(['name', 'id', 'user_id', 'balance'])
            ->setDescriptionForEvent(function (string $eventName) use ($wallet) {
                $actorName = Auth::user() ? Auth::user()->name : 'Cronjob ';
                return "{$actorName} has {$eventName} wallet_id of {$wallet->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
