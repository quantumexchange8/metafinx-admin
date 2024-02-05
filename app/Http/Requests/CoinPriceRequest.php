<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoinPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required'],
            'date' => ['required', 'date', 'after_or_equal:today']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'price' => 'MXT Coin Price',
            'date' => 'Price Date',
        ];
    }
}
