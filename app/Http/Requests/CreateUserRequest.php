<?php

namespace App\Http\Requests;

use App\Rules\NotBreachedPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'registration_no' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'facility_id' => ['required', 'exists:facilities,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'primary_role_id' => ['nullable', 'exists:roles,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'preferred_locale' => ['nullable', Rule::in(['en', 'sw'])],
            'must_change_password' => ['nullable', 'boolean'],
            'password' => [
                'required', 'confirmed', 'string',
                'min:'.config('hms.password.min_length', 12),
                'max:255',
                new NotBreachedPassword,
            ],
        ];
    }
}