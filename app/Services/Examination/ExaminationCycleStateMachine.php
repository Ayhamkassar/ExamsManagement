<?php

namespace App\Services\Examination;

use App\Enums\AuditEventType;
use App\Enums\ExaminationCycleStatus;
use App\Models\ExaminationCycle;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ExaminationCycleStateMachine
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function transition(ExaminationCycle $cycle, ExaminationCycleStatus $newStatus, ?string $performedBy = null): ExaminationCycle
    {
        $oldStatus = $cycle->status;

        if (!$this->canTransition($cycle->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid state transition from {$oldStatus->value} to {$newStatus->value}"
            );
        }

        return DB::transaction(function () use ($cycle, $newStatus, $oldStatus, $performedBy) {
            $cycle->status = $newStatus->value;
            $cycle->save();

            $this->auditLogger->log(
                AuditEventType::ExaminationCycleTransitioned->value,
                $cycle,
                ['status' => $oldStatus->value],
                ['status' => $newStatus->value],
                [
                    'organization_id' => $cycle->tenant_id,
                    'performed_by' => $performedBy,
                    'transition' => $oldStatus->value . ' -> ' . $newStatus->value,
                ]
            );

            return $cycle->fresh();
        });
    }

    public function canTransition(ExaminationCycleStatus $from, ExaminationCycleStatus $to): bool
    {
        $allowedTransitions = [
            ExaminationCycleStatus::Draft => [
                ExaminationCycleStatus::Scheduled,
                ExaminationCycleStatus::Cancelled,
            ],
            ExaminationCycleStatus::Scheduled => [
                ExaminationCycleStatus::Active,
                ExaminationCycleStatus::Cancelled,
            ],
            ExaminationCycleStatus::Active => [
                ExaminationCycleStatus::Completed,
                ExaminationCycleStatus::Cancelled,
            ],
            ExaminationCycleStatus::Completed => [
                ExaminationCycleStatus::Archived,
            ],
            ExaminationCycleStatus::Cancelled => [], // Terminal state
            ExaminationCycleStatus::Archived => [], // Terminal state
        ];

        return in_array($to, $allowedTransitions[$from] ?? []);
    }

    public function getAvailableTransitions(ExaminationCycleStatus $current): array
    {
        return [
            ExaminationCycleStatus::Draft => ['scheduled', 'cancelled'],
            ExaminationCycleStatus::Scheduled => ['active', 'cancelled'],
            ExaminationCycleStatus::Active => ['completed', 'cancelled'],
            ExaminationCycleStatus::Completed => ['archived'],
            ExaminationCycleStatus::Cancelled => [],
            ExaminationCycleStatus::Archived => [],
        ][$current] ?? [];
    }
}
