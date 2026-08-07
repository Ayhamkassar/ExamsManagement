<?php

namespace App\Services\Examination;

use App\Enums\AuditEventType;
use App\Enums\ExaminationStatus;
use App\Models\Examination;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ExaminationStateMachine
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function transition(Examination $examination, ExaminationStatus $newStatus, ?string $performedBy = null): Examination
    {
        $oldStatus = $examination->status;

        if (!$this->canTransition($examination->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid state transition from {$oldStatus->value} to {$newStatus->value}"
            );
        }

        return DB::transaction(function () use ($examination, $newStatus, $oldStatus, $performedBy) {
            $examination->status = $newStatus->value;
            $examination->save();

            $this->auditLogger->log(
                AuditEventType::ExaminationTransitioned->value,
                $examination,
                ['status' => $oldStatus->value],
                ['status' => $newStatus->value],
                [
                    'organization_id' => $examination->tenant_id,
                    'performed_by' => $performedBy,
                    'transition' => $oldStatus->value . ' -> ' . $newStatus->value,
                ]
            );

            return $examination->fresh();
        });
    }

    public function canTransition(ExaminationStatus $from, ExaminationStatus $to): bool
    {
        $allowedTransitions = [
            ExaminationStatus::Draft => [
                ExaminationStatus::Scheduled,
                ExaminationStatus::Cancelled,
            ],
            ExaminationStatus::Scheduled => [
                ExaminationStatus::Active,
                ExaminationStatus::Cancelled,
            ],
            ExaminationStatus::Active => [
                ExaminationStatus::Completed,
                ExaminationStatus::Cancelled,
            ],
            ExaminationStatus::Completed => [
                ExaminationStatus::Archived,
            ],
            ExaminationStatus::Cancelled => [],
            ExaminationStatus::Archived => [],
        ];

        return in_array($to, $allowedTransitions[$from] ?? []);
    }

    public function getAvailableTransitions(ExaminationStatus $current): array
    {
        return [
            ExaminationStatus::Draft => ['scheduled', 'cancelled'],
            ExaminationStatus::Scheduled => ['active', 'cancelled'],
            ExaminationStatus::Active => ['completed', 'cancelled'],
            ExaminationStatus::Completed => ['archived'],
            ExaminationStatus::Cancelled => [],
            ExaminationStatus::Archived => [],
        ][$current] ?? [];
    }
}
