<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EditMemberRequest extends FormRequest
{
    public function rules(): array
    {
        $user_id = $this->request->get('user_id');

        return [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'phone' => ['required', 'max:255', Rule::unique(User::class)->ignore($user_id)],
            'password' => ['nullable', Password::defaults()],
            'identity_number' => ['required'],
            'rank' => ['required'],
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
            'password' => 'Password',
            'identity_number' => 'Identity Number',
            'rank' => 'Ranking',
            'upline_id' => 'Upline',
        ];
    }
}
