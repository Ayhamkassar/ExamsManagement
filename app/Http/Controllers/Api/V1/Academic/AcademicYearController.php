<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Academic\AcademicYearResource;
use App\Models\AcademicYear;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AcademicYearController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('viewAny', AcademicYear::class);

        $academicYears = AcademicYear::query()
            ->where('tenant_id', $organization->id)
            ->paginate();

        return ApiResponse::success(
            AcademicYearResource::collection($academicYears),
        );
    }

    public function store(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('create', AcademicYear::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'before:end_date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $validated['tenant_id'] = $organization->id;

        $academicYear = AcademicYear::create($validated);

        $this->auditLogger->log(
            AuditEventType::AcademicYearCreated->value,
            $academicYear,
            null,
            $academicYear->toArray(),
            [
                'organization_id' => $organization->id,
            ],
        );

        $this->securityEventLogger->record(
            'academic_year.created',
            $user,
            [
                'organization_id' => $organization->id,
                'academic_year_id' => $academicYear->id,
                'name' => $academicYear->name,
            ],
        );

        return ApiResponse::created(
            new AcademicYearResource($academicYear),
            'Academic year created successfully.',
        );
    }

    public function show(Request $request, Tenant $organization, AcademicYear $academicYear): JsonResponse
    {
        $user = $request->user();

        if ($academicYear->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic year not found.', 404);
        }

        Gate::authorize('view', $academicYear);

        return ApiResponse::success(
            new AcademicYearResource($academicYear),
        );
    }

    public function update(Request $request, Tenant $organization, AcademicYear $academicYear): JsonResponse
    {
        $user = $request->user();

        if ($academicYear->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic year not found.', 404);
        }

        Gate::authorize('update', $academicYear);

        $oldValues = $academicYear->toArray();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date', 'before:end_date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        $academicYear->update($validated);

        $this->auditLogger->log(
            AuditEventType::AcademicYearUpdated->value,
            $academicYear,
            $oldValues,
            $academicYear->fresh()->toArray(),
            [
                'organization_id' => $organization->id,
            ],
        );

        return ApiResponse::success(
            new AcademicYearResource($academicYear->fresh()),
            'Academic year updated successfully.',
        );
    }

    public function destroy(Request $request, Tenant $organization, AcademicYear $academicYear): JsonResponse
    {
        $user = $request->user();

        if ($academicYear->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic year not found.', 404);
        }

        Gate::authorize('delete', $academicYear);

        $oldValues = $academicYear->toArray();

        $academicYear->delete();

        $this->auditLogger->log(
            AuditEventType::AcademicYearDeleted->value,
            $academicYear,
            $oldValues,
            null,
            [
                'organization_id' => $organization->id,
            ],
        );

        return ApiResponse::success(
            null,
            'Academic year deleted successfully.',
        );
    }
}
