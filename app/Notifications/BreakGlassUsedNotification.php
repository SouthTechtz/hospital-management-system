<?php

namespace App\Notifications;

use App\Models\BreakGlassAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to every privacy officer when break-glass access is used (Part B).
 * Plain factual language, no greetings or celebratory text (Part D2/D6).
 */
class BreakGlassUsedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly BreakGlassAccess $access)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $access = $this->access->loadMissing(['user', 'patient']);

        return (new MailMessage)
            ->subject(__('break_glass.notification.subject'))
            ->line(__('break_glass.notification.body', [
                'user' => $access->user?->full_name ?? $access->user_id,
                'patient' => $access->patient?->mrn ?? $access->patient_id,
                'reason' => $access->reason_code,
            ]))
            ->line(__('break_glass.notification.expires', [
                'time' => $access->expires_at->format(config('hms.clinical.dt_formats.datetime')),
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $access = $this->access;

        return [
            'type' => 'break_glass',
            'break_glass_id' => $access->getKey(),
            'patient_id' => $access->patient_id,
            'user_id' => $access->user_id,
            'reason_code' => $access->reason_code,
            'expires_at' => $access->expires_at->toIso8601String(),
        ];
    }
}
