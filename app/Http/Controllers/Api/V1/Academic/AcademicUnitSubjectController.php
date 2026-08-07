<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Academic\SubjectResource;
use App\Models\AcademicUnit;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AcademicUnitSubjectController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request, Tenant $organization, AcademicUnit $academicUnit): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        Gate::authorize('view', $academicUnit);

        $subjects = $academicUnit->subjects()->paginate();

        return ApiResponse::success(
            SubjectResource::collection($subjects),
        );
    }

    public function store(Request $request, Tenant $organization, AcademicUnit $academicUnit): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        Gate::authorize('update', $academicUnit);

        $validated = $request->validate([
            'subject_id' => ['required', 'ulid', 'exists:subjects,id'],
            'academic_year_id' => ['nullable', 'ulid', 'exists:academic_years,id'],
        ]);

        $subject = Subject::query()
            ->where('id', $validated['subject_id'])
            ->where('tenant_id', $organization->id)
            ->firstOrFail();

        $academicYearId = $validated['academic_year_id'] ?? null;

        if ($academicYearId) {
            $academicYear = AcademicYear::query()
                ->where('id', $academicYearId)
                ->where('tenant_id', $organization->id)
                ->firstOrFail();
        }

        $academicUnit->subjects()->attach($subject->id, [
            'academic_year_id' => $academicYearId,
        ]);

        $this->auditLogger->log(
            AuditEventType::SubjectAssigned->value,
            $academicUnit,
            null,
            [
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYearId,
            ],
            ['organization_id' => $organization->id],
        );

        $this->securityEventLogger->record(
            'academic_unit_subject.created',
            $request->user(),
            [
                'organization_id' => $organization->id,
                'academic_unit_id' => $academicUnit->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYearId,
            ],
        );

        return ApiResponse::success(
            new SubjectResource($subject),
            'Subject assigned successfully.',
            201,
        );
    }

    public function destroy(Request $request, Tenant $organization, AcademicUnit $academicUnit, Subject $subject): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        if ($subject->tenant_id !== $organization->id) {
            return ApiResponse::error('Subject not found.', 404);
        }

        Gate::authorize('update', $academicUnit);

        $academicUnit->subjects()->detach($subject->id);

        $this->auditLogger->log(
            AuditEventType::SubjectUnassigned->value,
            $academicUnit,
            ['subject_id' => $subject->id],
            null,
            ['organization_id' => $organization->id],
        );

        return ApiResponse::success(
            null,
            'Subject unassigned successfully.',
        );
    }
}
