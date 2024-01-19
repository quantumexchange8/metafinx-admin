<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketBonusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        $value = '';

        if ($this->slug === 'withdrawal-fee') {
            $value = 'Withdrawal Fee';
        } elseif ($this->slug === 'gas-fee') {
            $value = 'Gas Fee';
        }

        return [
            'value' => $value,
        ];
    }
}
