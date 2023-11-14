<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AddMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:8', 'unique:' . User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Password::defaults()],
            'verification_type' => ['required'],
            'identity_number' => ['required', 'unique:' . User::class],
            'ranking' => ['required'],
            'upline_id' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'password' => 'Password',
            'verification_type' => 'Verification Type',
            'identity_number' => 'Identity Number',
            'ranking' => 'Ranking',
            'upline_id' => 'Upline',
        ];
    }
}
