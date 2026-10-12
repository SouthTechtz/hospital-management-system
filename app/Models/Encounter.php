<?php

namespace App\Models;

use App\Enums\EncounterStatus;
use App\Enums\EncounterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encounter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id', 'patient_id', 'visit_number', 'encounter_type', 'status',
        'department_id', 'clinician_id', 'location', 'payment_type',
        'attending_encounter_id', 'started_at', 'ended_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'encounter_type' => EncounterType::class,
            'status' => EncounterStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clinician_id');
    }

    public function triage(): HasOne
    {
        return $this->hasOne(TriageAssessment::class)->whereNull('voided_at')->latestOfMany();
    }

    public function triageAssessments(): HasMany
    {
        return $this->hasMany(TriageAssessment::class);
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function currentConsultation(): HasOne
    {
        return $this->hasOne(Consultation::class)->whereNull('voided_at')->latestOfMany();
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function latestVitals(): HasOne
    {
        return $this->hasOne(VitalSign::class)->latestOfMany();
    }
}
