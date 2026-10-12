<?php

namespace App\Http\Requests;

use App\Enums\EncounterType;
use App\Enums\PayerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Encounter::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'encounter_type' => ['required', Rule::enum(EncounterType::class)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:120'],
            'payment_type' => ['nullable', Rule::enum(PayerType::class)],
            'queue_id' => ['nullable', 'exists:queues,id'],
        ];
    }
}