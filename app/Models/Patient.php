<?php

namespace App\Models;

use App\Enums\PayerType;
use App\Enums\Sex;
use App\Support\SensitiveHasher;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id', 'mrn', 'first_name', 'middle_name', 'last_name',
        'full_name_normalized', 'sex', 'date_of_birth', 'birth_date_estimated',
        'estimated_age_years', 'national_id', 'nida_number', 'nida_verified_at',
        'phone', 'alt_phone', 'email', 'region', 'district', 'ward', 'street',
        'marital_status', 'occupation', 'religion', 'nationality', 'payer_type',
        'nhif_number', 'nhif_verified_at', 'blood_group', 'patient_category',
        'photo_path', 'notes', 'no_known_allergies_confirmed', 'merge_status',
        'merged_into_patient_id', 'deceased_at', 'death_certificate_no',
        'registered_by', 'registered_at', 'created_by', 'updated_by',
    ];

    protected $hidden = [
        'national_id', 'nida_number', 'nhif_number', 'phone', 'alt_phone', 'email',
    ];

    protected function casts(): array
    {
        return [
            'sex' => Sex::class,
            'payer_type' => PayerType::class,
            'date_of_birth' => 'date',
            'birth_date_estimated' => 'boolean',
            'no_known_allergies_confirmed' => 'boolean',
            'nida_verified_at' => 'datetime',
            'nhif_verified_at' => 'datetime',
            'registered_at' => 'datetime',
            'deceased_at' => 'datetime',
            'national_id' => 'encrypted',
            'nida_number' => 'encrypted',
            'nhif_number' => 'encrypted',
            'phone' => 'encrypted',
            'alt_phone' => 'encrypted',
            'email' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Patient $patient) {
            $patient->full_name_normalized = self::normalizeName(
                $patient->first_name.' '.$patient->middle_name.' '.$patient->last_name
            );
            $patient->national_id_hash = SensitiveHasher::hash($patient->national_id);
            $patient->nida_hash = SensitiveHasher::hash($patient->nida_number);
            $patient->nhif_hash = SensitiveHasher::hash($patient->nhif_number);
            $patient->phone_hash = SensitiveHasher::phone($patient->phone);
            $patient->alt_phone_hash = SensitiveHasher::phone($patient->alt_phone);
        });
    }

    public static function normalizeName(?string $name): string
    {
        $name = mb_strtolower((string) $name);
        $name = str_replace(['.', ',', "'", '-'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return trim($name);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name, $this->middle_name, $this->last_name,
        ])));
    }

    public function getAgeAttribute(): ?int
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->age;
        }

        return $this->estimated_age_years;
    }

    public function ageDisplay(): string
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->diff(now())->format('%y');
        }

        return $this->estimated_age_years !== null ? (string) $this->estimated_age_years : __('ui.unknown');
    }

    public function isDeceased(): bool
    {
        return $this->deceased_at !== null;
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(PatientNextOfKin::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    /**
     * Consents that are currently granted and not withdrawn.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consent>
     */
    public function activeConsents(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->consents()
            ->where('granted', true)
            ->whereNull('withdrawn_at')
            ->get();
    }

    public function hasActiveConsent(\App\Enums\ConsentPurpose $purpose): bool
    {
        return $this->consents()
            ->where('purpose', $purpose->value)
            ->where('granted', true)
            ->whereNull('withdrawn_at')
            ->exists();
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    public function activeAllergies(): HasMany
    {
        return $this->allergies()->where('status', 'active')->whereNull('deleted_at');
    }

    /**
     * Exemptions that are active and valid today.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exemption>
     */
    public function activeExemptions(): \Illuminate\Database\Eloquent\Collection
    {
        return Exemption::query()
            ->where('patient_id', $this->getKey())
            ->where('is_active', true)
            ->get()
            ->filter(fn (Exemption $exemption) => $exemption->isCurrent())
            ->values();
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function merges(): HasMany
    {
        return $this->hasMany(PatientMerge::class, 'source_patient_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('merge_status', 'active')->whereNull('deceased_at');
    }

    /**
     * Deterministic candidate set for probabilistic duplicate detection:
     * same exact identifier, or a name/DOB/sex match.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Patient>
     */
    public function duplicateCandidates(): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->whereKeyNot($this->getKey())
            ->where('facility_id', $this->facility_id)
            ->where(function (Builder $query) {
                $pairs = [
                    ['national_id_hash', $this->national_id_hash],
                    ['nida_hash', $this->nida_hash],
                    ['nhif_hash', $this->nhif_hash],
                    ['phone_hash', $this->phone_hash],
                ];

                foreach ($pairs as [$column, $hash]) {
                    if ($hash) {
                        $query->orWhere($column, $hash);
                    }
                }

                $query->orWhere(function (Builder $inner) {
                    $inner->where('full_name_normalized', $this->full_name_normalized)
                        ->where('date_of_birth', $this->date_of_birth);
                });
            })
            ->limit(25)
            ->get();
    }
}
