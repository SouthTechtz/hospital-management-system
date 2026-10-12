<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Consultation;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Orders (prescriptions are orders in Phase 1) with void-only correction.
 */
class OrderService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $meta
     */
    public function createMedicationOrder(
        Patient $patient,
        ?Consultation $consultation,
        array $items,
        User $user,
        array $meta = [],
    ): Order {
        if ($items === []) {
            throw new \InvalidArgumentException('An order must contain at least one item.');
        }

        return DB::transaction(function () use ($patient, $consultation, $items, $user, $meta) {
            $order = Order::create([
                'facility_id' => $patient->facility_id,
                'patient_id' => $patient->getKey(),
                'encounter_id' => $consultation?->encounter_id,
                'consultation_id' => $consultation?->getKey(),
                'order_type' => OrderType::Medication->value,
                'status' => OrderStatus::Ordered,
                'priority' => $meta['priority'] ?? OrderPriority::Routine->value,
                'notes' => $meta['notes'] ?? null,
                'ordered_by' => $user->getKey(),
                'ordered_at' => now(),
            ]);

            foreach ($items as $row) {
                $order->items()->create([
                    'item_type' => 'medication',
                    'drug_id' => $row['drug_id'] ?? null,
                    'description' => $row['description'] ?? null,
                    'dose' => $row['dose'] ?? null,
                    'dose_unit' => $row['dose_unit'] ?? null,
                    'route' => $row['route'] ?? null,
                    'frequency' => $row['frequency'] ?? null,
                    'duration_days' => $row['duration_days'] ?? null,
                    'quantity' => $row['quantity'] ?? null,
                    'quantity_unit' => $row['quantity_unit'] ?? 'dose',
                    'is_prn' => (bool) ($row['is_prn'] ?? false),
                    'instructions' => $row['instructions'] ?? null,
                ]);
            }

            $this->audit->log(AuditAction::Create, $order, $patient, 'Medication order placed', [], [
                'items' => count($items),
                'priority' => $order->priority,
            ], $user);

            return $order;
        });
    }

    public function voidOrder(Order $order, string $reason, User $user): void
    {
        $order->forceFill(['voided_at' => now(), 'void_reason' => $reason, 'status' => OrderStatus::Cancelled])->save();

        $this->audit->log(AuditAction::Void, $order, $order->patient, 'Order voided', [], [
            'reason' => $reason,
        ], $user);
    }
}