<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingStakingReward extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'month',
        'release_date',
        'percent',
        'is_done',
        'updated_by',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

}
