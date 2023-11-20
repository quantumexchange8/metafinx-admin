<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketBonusRequest extends FormRequest
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
            'amount' => 'Ticket Bonus Amount',
            'date' => 'Ticket Bonus Release Date',
        ];
    }
}
