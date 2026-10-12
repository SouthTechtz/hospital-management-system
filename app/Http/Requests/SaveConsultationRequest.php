<?php

namespace App\Http\Requests;

use App\Enums\DiagnosisType;
use App\Enums\OrderPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $encounter = $this->route('encounter');

        return $this->user()->can('create', [$encounter]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'history' => ['nullable', 'string', 'max:20000'],
            'examination' => ['nullable', 'string', 'max:20000'],
            'plan' => ['nullable', 'string', 'max:20000'],
            'disposition' => ['nullable', 'in:discharge,admit,refer,follow_up'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'correction_reason' => ['nullable', 'string', 'max:500'],
            'signed' => ['nullable', 'boolean'],

            'diagnoses' => ['nullable', 'array', 'max:40'],
            'diagnoses.*.icd10_code' => ['nullable', 'string', 'max:12'],
            'diagnoses.*.icd10_description' => ['nullable', 'string', 'max:300'],
            'diagnoses.*.icd10_code_id' => ['nullable', 'exists:icd10_codes,id'],
            'diagnoses.*.type' => ['nullable', Rule::enum(DiagnosisType::class)],
            'diagnoses.*.notes' => ['nullable', 'string', 'max:1000'],

            'order_items' => ['nullable', 'array', 'max:80'],
            'order_items.*.drug_id' => ['nullable', 'exists:drugs,id'],
            'order_items.*.description' => ['nullable', 'string', 'max:300'],
            'order_items.*.dose' => ['nullable', 'string', 'max:40'],
            'order_items.*.dose_unit' => ['nullable', 'string', 'max:30'],
            'order_items.*.route' => ['nullable', 'string', 'max:40'],
            'order_items.*.frequency' => ['nullable', 'string', 'max:60'],
            'order_items.*.duration_days' => ['nullable', 'integer', 'between:1,365'],
            'order_items.*.quantity' => ['nullable', 'numeric', 'between:0.01,100000'],
            'order_items.*.is_prn' => ['nullable', 'boolean'],
            'order_items.*.instructions' => ['nullable', 'string', 'max:1000'],
            'order_priority' => ['nullable', Rule::enum(OrderPriority::class)],
            'order_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}