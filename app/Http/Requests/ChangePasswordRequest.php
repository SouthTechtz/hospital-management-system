<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:'.config('hms.password.min_length', 12),
                'not_in:password,123456789,12345678,iloveyou,password1,12345678,abc123,123456,1234,iloveu,qwerty',
                'max:255',
                new \App\Rules\NotBreachedPassword,
            ],
        ];
    }
}