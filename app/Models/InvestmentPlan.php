<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvestmentPlan extends Model
{
    use SoftDeletes, HasTranslations, LogsActivity;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'investment_min_amount',
        'investment_period',
        'roi_per_annum',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $investmentPlan = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('investment_plan')
            ->logOnly(['id', 'name', 'investment_min_amount', 'investment_period', 'roi_per_annum', 'status'])
            ->setDescriptionForEvent(function (string $eventName) use ($investmentPlan) {
                return Auth::user()->name . " has {$eventName} investment_plan_id of {$investmentPlan->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    
    public function descriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvestmentPlanDescription::class, 'investment_plan_id', 'id');
    }
}
