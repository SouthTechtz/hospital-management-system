<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\BreakGlassStatus;
use App\Models\BreakGlassAccess;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\BreakGlassUsedNotification;

class BreakGlassService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /**
     * Grant a time-limited break-glass access. Requires a coded reason and
     * notifies the privacy officer (Part B).
     */
    public function activate(User $user, Patient $patient, string $reasonCode, string $reasonText, array $scope = []): BreakGlassAccess
    {
        $access = BreakGlassAccess::create([
            'facility_id' => $patient->facility_id,
            'user_id' => $user->getKey(),
            'patient_id' => $patient->getKey(),
            'reason_code' => $reasonCode,
            'reason_text' => $reasonText,
            'scope' => $scope,
            'granted_at' => now(),
            'expires_at' => now()->addMinutes((int) config('hms.break_glass.duration_minutes', 60)),
            'status' => BreakGlassStatus::Active,
        ]);

        $this->notifyPrivacyOfficers($access);

        $access->forceFill([
            'privacy_officer_notified' => true,
            'privacy_officer_notified_at' => now(),
        ])->save();

        $this->audit->log(
            AuditAction::BreakGlass,
            $access,
            $patient,
            'Break-glass access granted: '.$reasonCode,
            [],
            ['reason_code' => $reasonCode, 'expires_at' => $access->expires_at->toIso8601String()],
            $user,
        );

        return $access;
    }

    public function clientBreakGlass(User $user, Patient $patient): ?BreakGlassAccess
    {
        return BreakGlassAccess::query()
            ->where('user_id', $user->getKey())
            ->where('patient_id', $patient->getKey())
            ->where('status', BreakGlassStatus::Active->value)
            ->where('expires_at', '>', now())
            ->latest('granted_at')
            ->first();
    }

    public function recordReview(BreakGlassAccess $access, User $officer, string $outcome, ?string $notes): void
    {
        $access->forceFill([
            'status' => BreakGlassStatus::Reviewed,
            'reviewed_by' => $officer->getKey(),
            'reviewed_at' => now(),
            'review_outcome' => $outcome,
            'review_notes' => $notes,
        ])->save();

        $this->audit->log(
            AuditAction::Update,
            $access,
            $access->patient,
            'Break-glass access reviewed: '.$outcome,
            ['status' => BreakGlassStatus::Active->value],
            ['status' => $outcome, 'review_outcome' => $outcome],
            $officer,
        );
    }

    private function notifyPrivacyOfficers(BreakGlassAccess $access): void
    {
        if (! config('hms.break_glass.notify_privacy_officer')) {
            return;
        }

        User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'privacy_officer'))
            ->where('is_active', true)
            ->get()
            ->each(fn (User $officer) => $officer->notify(new BreakGlassUsedNotification($access)));
    }
}
