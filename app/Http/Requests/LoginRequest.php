<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        $rules = [
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ];

        if ($this->input('mfa_code')) {
            $rules['mfa_code'] = ['required', 'string', 'size:6'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'username' => __('auth.username'),
            'password' => __('auth.password'),
            'mfa_code' => __('auth.mfa_code'),
        ];
    }
}