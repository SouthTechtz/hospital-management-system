<?php

namespace App\Http\Requests;

use App\Enums\PayerType;
use App\Enums\Sex;
use App\Rules\NotBreachedPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Patient::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'sex' => ['required', Rule::enum(Sex::class)],
            'date_of_birth' => ['required_without:estimated_age_years', 'nullable', 'date', 'before_or_equal:today'],
            'estimated_age_years' => ['required_without:date_of_birth', 'nullable', 'integer', 'between:0,120'],
            'birth_date_estimated' => ['nullable', 'boolean'],
            'national_id' => ['nullable', 'digits_between:8,20'],
            'nida_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:150'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'religion' => ['nullable', 'string', 'max:60'],
            'nationality' => ['nullable', 'string', 'max:60'],
            'payer_type' => ['required', Rule::enum(PayerType::class)],
            'nhif_number' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-,unknown'],
            'patient_category' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'no_known_allergies_confirmed' => ['nullable', 'boolean'],
            'consent_method' => ['nullable', 'in:verbal,written,digital'],
            'consents' => ['nullable', 'array'],
            'consents.treatment' => ['nullable', 'boolean'],
            'consents.data_sharing' => ['nullable', 'boolean'],
            'consents.research' => ['nullable', 'boolean'],
            'next_of_kin' => ['nullable', 'array'],
            'next_of_kin.*.full_name' => ['required_with:next_of_kin', 'string', 'max:150'],
            'next_of_kin.*.relationship' => ['required_with:next_of_kin', 'string', 'max:60'],
            'next_of_kin.*.phone' => ['nullable', 'string', 'max:20'],
            'next_of_kin.*.alt_phone' => ['nullable', 'string', 'max:20'],
            'next_of_kin.*.address' => ['nullable', 'string', 'max:200'],
            'next_of_kin.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_of_birth' => __('patients.date_of_birth'),
            'estimated_age_years' => __('patients.estimated_age'),
        ];
    }
}