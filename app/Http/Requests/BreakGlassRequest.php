<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BreakGlassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('breakGlass', \App\Models\Patient::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'reason_code' => ['required', Rule::in([
                'emergency_care', 'unassigned_emergency', 'patient_unresponsive',
                'audit_request', 'other',
            ])],
            'reason_text' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}