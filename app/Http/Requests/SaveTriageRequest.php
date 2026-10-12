<?php

namespace App\Http\Requests;

use App\Enums\TriageCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('triage.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'encounter_id' => ['required', 'exists:encounters,id'],
            'category' => ['required', Rule::enum(TriageCategory::class)],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'queue_id' => ['nullable', 'exists:queues,id'],
            'vitals' => ['nullable', 'array'],
            'vitals.temperature_c' => ['nullable', 'numeric', 'between:30,45'],
            'vitals.pulse' => ['nullable', 'integer', 'between:0,300'],
            'vitals.respiratory_rate' => ['nullable', 'integer', 'between:0,150'],
            'vitals.systolic' => ['nullable', 'integer', 'between:0,300'],
            'vitals.diastolic' => ['nullable', 'integer', 'between:0,200'],
            'vitals.spo2' => ['nullable', 'integer', 'between:0,100'],
            'vitals.weight_kg' => ['nullable', 'numeric', 'between:0.3,300'],
            'vitals.height_cm' => ['nullable', 'numeric', 'between:10,250'],
            'vitals.muac_mm' => ['nullable', 'integer', 'between:50,600'],
            'vitals.pain_score' => ['nullable', 'integer', 'between:0,10'],
            'vitals.gcs' => ['nullable', 'integer', 'between:3,15'],
            'vitals.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}