<?php

namespace App\Services\Examination;

use App\Enums\AuditEventType;
use App\Enums\ExaminationSessionStatus;
use App\Models\ExaminationSession;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ExaminationSessionStateMachine
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function transition(ExaminationSession $session, ExaminationSessionStatus $newStatus, ?string $performedBy = null): ExaminationSession
    {
        $oldStatus = $session->status;

        if (!$this->canTransition($session->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid state transition from {$oldStatus->value} to {$newStatus->value}"
            );
        }

        return DB::transaction(function () use ($session, $newStatus, $oldStatus, $performedBy) {
            $session->status = $newStatus->value;
            $session->save();

            $this->auditLogger->log(
                AuditEventType::ExaminationSessionTransitioned->value,
                $session,
                ['status' => $oldStatus->value],
                ['status' => $newStatus->value],
                [
                    'organization_id' => $session->tenant_id,
                    'performed_by' => $performedBy,
                    'transition' => $oldStatus->value . ' -> ' . $newStatus->value,
                ]
            );

            return $session->fresh();
        });
    }

    public function canTransition(ExaminationSessionStatus $from, ExaminationSessionStatus $to): bool
    {
        $allowedTransitions = [
            ExaminationSessionStatus::Scheduled => [
                ExaminationSessionStatus::Open,
                ExaminationSessionStatus::Cancelled,
            ],
            ExaminationSessionStatus::Open => [
                ExaminationSessionStatus::InProgress,
                ExaminationSessionStatus::Cancelled,
            ],
            ExaminationSessionStatus::InProgress => [
                ExaminationSessionStatus::Completed,
                ExaminationSessionStatus::Cancelled,
            ],
            ExaminationSessionStatus::Completed => [], // Terminal state
            ExaminationSessionStatus::Cancelled => [], // Terminal state
        ];

        return in_array($to, $allowedTransitions[$from] ?? []);
    }

    public function getAvailableTransitions(ExaminationSessionStatus $current): array
    {
        return [
            ExaminationSessionStatus::Scheduled => ['open', 'cancelled'],
            ExaminationSessionStatus::Open => ['in_progress', 'cancelled'],
            ExaminationSessionStatus::InProgress => ['completed', 'cancelled'],
            ExaminationSessionStatus::Completed => [],
            ExaminationSessionStatus::Cancelled => [],
        ][$current] ?? [];
    }
}
