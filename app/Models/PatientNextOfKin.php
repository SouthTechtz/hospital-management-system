<?php

namespace App\Models;

use App\Support\SensitiveHasher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNextOfKin extends Model
{
    use HasFactory;

    protected $table = 'patient_next_of_kin';

    protected $fillable = [
        'patient_id', 'full_name', 'relationship', 'phone', 'alt_phone',
        'address', 'is_primary',
    ];

    protected $hidden = ['phone', 'alt_phone'];

    protected function casts(): array
    {
        return [
            'phone' => 'encrypted',
            'alt_phone' => 'encrypted',
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $kin) {
            $kin->phone_hash = SensitiveHasher::phone($kin->phone);
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
