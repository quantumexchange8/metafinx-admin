<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => ['required'],
            'details' => ['required'],
            'receiver_type' => ['required'],
            'receiver' => ['nullable', 'required_if:receiver_type,specific_member'],
            'image' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'subject' => 'Subject',
            'details' => 'Details',
            'receiver_type' => 'Receiver Type',
            'receiver' => 'Receiver',
            'image' => 'Attachment',
        ];
    }
}
