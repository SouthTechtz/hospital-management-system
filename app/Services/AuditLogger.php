<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * Writes append-only audit entries (Part B). Logging must never break a
 * clinical transaction, so failures are swallowed and reported to the log
 * channel. Sensitive values are redacted before they reach the database.
 */
class AuditLogger
{
    public function log(
        AuditAction $action,
        ?Model $auditable = null,
        ?Patient $patient = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $actor = null,
    ): ?AuditLog {
        try {
            /** @var User|null $actor */
            $actor ??= Auth::user();
            $request = Request::instance();

            return AuditLog::create([
                'facility_id' => $actor?->facility_id ?? $patient?->facility_id,
                'user_id' => $actor?->getKey(),
                'actor_name' => $actor?->full_name,
                'actor_role' => $actor?->primaryRole?->name ?? $actor?->roles->first()?->name,
                'action' => $action->value,
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'patient_id' => $patient?->getKey(),
                'description' => $description,
                'old_values' => $oldValues ? $this->redact($oldValues) : null,
                'new_values' => $newValues ? $this->redact($newValues) : null,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'device' => $this->device($request->userAgent()),
                'occurred_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    public function patientView(Patient $patient, string $context): void
    {
        $this->log(AuditAction::View, $patient, $patient, $context);
    }

    public function created(Model $model, ?Patient $patient = null): void
    {
        $this->log(AuditAction::Create, $model, $patient ?? $this->patientFrom($model), null, [], $model->getAttributes());
    }

    public function updated(Model $model, array $before, ?Patient $patient = null): void
    {
        $this->log(AuditAction::Update, $model, $patient ?? $this->patientFrom($model), null, $before, $model->getAttributes());
    }

    public function deleted(Model $model, ?Patient $patient = null): void
    {
        $this->log(AuditAction::Delete, $model, $patient ?? $this->patientFrom($model), null, $model->getAttributes(), []);
    }

    private function patientFrom(Model $model): ?Patient
    {
        if ($model instanceof Patient) {
            return $model;
        }

        try {
            $related = $model->getAttribute('patient_id');
            if ($related) {
                return Patient::find($related);
            }
        } catch (Throwable) {
            // no patient relation
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function redact(array $values): array
    {
        $redacted = [];

        foreach ($values as $key => $value) {
            if (in_array(mb_strtolower((string) $key), config('hms.audit.redact', []), true)) {
                $redacted[$key] = '[REDACTED]';
                continue;
            }

            $redacted[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $redacted;
    }

    private function device(?string $userAgent): ?string
    {
        if ($userAgent === null) {
            return null;
        }

        return match (true) {
            str_contains($userAgent, 'Mobile') => 'mobile',
            str_contains($userAgent, 'Tablet') => 'tablet',
            default => 'desktop',
        };
    }
}
