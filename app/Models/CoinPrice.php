<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinPrice extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'setting_coin_id',
        'updated_by',
        'price',
        'price_date',
        'open_time',
        'close_time',
        'duration',
    ];

    protected $casts = [
        'price_date' => 'timestamp',
        'duration' => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $coinPrice = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('coin_price')
            ->logOnly(['id', 'setting_coin_id', 'updated_by', 'price', 'price_date', 'open_time', 'close_time', 'duration'])
            ->setDescriptionForEvent(function (string $eventName) use ($coinPrice) {
                $actorName = Auth::user() ? Auth::user()->name : 'System ';
                return "{$actorName} has {$eventName} coin price with ID: {$coinPrice->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

}
