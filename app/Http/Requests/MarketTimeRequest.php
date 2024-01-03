<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketTimeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'open_time_hr' => ['required', 'numeric'],
            'open_time_min' => ['required', 'numeric'],
            'open_time_meridiem' => ['required'],
            'close_time_hr' => ['required', 'numeric'],
            'close_time_min' => ['required', 'numeric'],
            'close_time_meridiem' => ['required'],
            'frequency' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return  [
            'open_time_hr' => 'Open Time Hr',
            'open_time_min' => 'Open Time Min',
            'open_time_meridiem' => 'Open Time AM/PM',
            'close_time_hr' => 'Close Time Hr',
            'close_time_min' => 'Close Time Min',
            'close_time_meridiem' => 'Close Time AM/PM',
            'frequency' => 'Frequency',
        ];
    }
}
