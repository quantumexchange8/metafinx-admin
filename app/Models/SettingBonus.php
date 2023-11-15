<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingBonus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'datetime',
    ];
}
