<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoinPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required'],
            'date' => ['required', 'date', 'unique:coin_prices,price_date']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'price' => 'XLC Coin Price',
            'date' => 'Price Date',
        ];
    }
}
