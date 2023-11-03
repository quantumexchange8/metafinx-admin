<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvestmentPlanDescription extends Model
{
    use SoftDeletes, HasTranslations, LogsActivity;

    public $translatable = ['description'];
    protected $fillable = [
        'investment_plan_id',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $investmentPlanDescription = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('investment_plan_description')
            ->logOnly(['id', 'investment_plan_id', 'description'])
            ->setDescriptionForEvent(function (string $eventName) use ($investmentPlanDescription) {
                return Auth::user()->name . " has {$eventName} investment_plan_description_id of {$investmentPlanDescription->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function investment_plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id', 'id');
    }
}
