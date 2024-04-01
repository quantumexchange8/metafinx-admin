<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StakingRewardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'month' => ['required'],
            'date' => ['required'],
            'percent' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'month' => 'Month',
            'date' => 'Date',
            'percent' => 'Percent',
        ];
    }
}
