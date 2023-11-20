<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DividendBonusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'amount' => 'Dividend Bonus Amount',
            'date' => 'Dividend Bonus Release Date',
        ];
    }
}
