<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coin extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'setting_coin_id',
        'address',
        'unit',
        'price',
        'amount',
    ];

    public function setting_coin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SettingCoin::class, 'setting_coin_id', 'id');
    }

    public function assetAdjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetAdjustment::class, 'coin_id', 'id')->whereNotNull('setting_coin_id');
    }

    public function settingCoinAdjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetAdjustment::class, 'setting_coin_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $coin = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('coin')
            ->logOnly(['id', 'user_id', 'setting_coin_id', 'address', 'unit', 'price', 'amount'])
            ->setDescriptionForEvent(function (string $eventName) use ($coin) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} coin with ID: {$coin->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
