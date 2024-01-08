<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoinAdjustmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'unit' => ['required', 'not_in:0'],
            'description' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'unit' => 'Unit',
            'description' => 'Description',
        ];
    }
}
