<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'setting_coin_id',
        'address',
        'unit',
        'price',
        'amount',
    ];
}
