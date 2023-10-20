<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentPlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plan_name.*' => 'required',
            'investment_min_amount' => 'required',
            'roi_per_annum' => 'required',
            'investment_period' => 'required',
            'descriptions.*.en' => 'required',
            'descriptions.*.cn' => 'required',
            'descriptions.*.tw' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        $languages = ['en' => 'English', 'cn' => 'Chinese Simplified', 'tw' => 'Chinese Traditional'];
        $attributes = [];

        foreach ($languages as $lang => $languageName) {
            $attributes["plan_name.$lang"] = "Plan name ($languageName)";
            $attributes["descriptions.*.$lang"] = "Description ($languageName)";
        }

        $attributes['investment_min_amount'] = 'Min. investment amount';
        $attributes['roi_per_annum'] = 'ROI per annum';
        $attributes['investment_period'] = 'Investment period (months)';

        return $attributes;
    }
}
