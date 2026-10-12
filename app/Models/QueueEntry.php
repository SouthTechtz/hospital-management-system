<?php

namespace App\Models;

use App\Enums\QueueEntryStatus;
use App\Enums\TriageCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    protected $fillable = [
        'facility_id', 'queue_id', 'patient_id', 'encounter_id', 'triage_category',
        'priority', 'status', 'added_at', 'called_at', 'completed_at',
        'added_by', 'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'triage_category' => TriageCategory::class,
            'status' => QueueEntryStatus::class,
            'added_at' => 'datetime',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function scopeWaiting($query)
    {
        return $query->whereIn('status', [
            QueueEntryStatus::Waiting->value,
            QueueEntryStatus::Called->value,
            QueueEntryStatus::InProgress->value,
        ]);
    }
}
