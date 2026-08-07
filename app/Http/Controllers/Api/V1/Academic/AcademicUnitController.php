<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Academic\AcademicUnitResource;
use App\Models\AcademicUnit;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AcademicUnitController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request, Tenant $organization): JsonResponse
    {
        Gate::authorize('viewAny', AcademicUnit::class);

        $academicUnits = AcademicUnit::query()
            ->where('tenant_id', $organization->id)
            ->with('parent')
            ->paginate();

        return ApiResponse::success(
            AcademicUnitResource::collection($academicUnits),
        );
    }

    public function store(Request $request, Tenant $organization): JsonResponse
    {
        Gate::authorize('create', AcademicUnit::class);

        $validated = $request->validate([
            'parent_id' => ['nullable', 'ulid', 'exists:academic_units,id'],
            'type' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        if (! empty($validated['parent_id'])) {
            $parent = AcademicUnit::query()
                ->where('id', $validated['parent_id'])
                ->where('tenant_id', $organization->id)
                ->first();

            if ($parent === null) {
                return ApiResponse::error(
                    'Parent unit does not belong to this organization.',
                    422,
                );
            }
        }

        $validated['tenant_id'] = $organization->id;

        $academicUnit = AcademicUnit::create($validated);

        $this->auditLogger->log(
            AuditEventType::AcademicUnitCreated->value,
            $academicUnit,
            null,
            $academicUnit->toArray(),
            ['organization_id' => $organization->id],
        );

        $this->securityEventLogger->record(
            'academic_unit.created',
            $request->user(),
            [
                'organization_id' => $organization->id,
                'academic_unit_id' => $academicUnit->id,
            ],
        );

        return ApiResponse::created(
            new AcademicUnitResource($academicUnit->load('parent')),
            'Academic unit created successfully.',
        );
    }

    public function show(Request $request, Tenant $organization, AcademicUnit $academicUnit): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        Gate::authorize('view', $academicUnit);

        return ApiResponse::success(
            new AcademicUnitResource($academicUnit->load('parent', 'children')),
        );
    }

    public function update(Request $request, Tenant $organization, AcademicUnit $academicUnit): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        Gate::authorize('update', $academicUnit);

        $oldValues = $academicUnit->toArray();

        $validated = $request->validate([
            'parent_id' => ['nullable', 'ulid', 'exists:academic_units,id'],
            'type' => ['sometimes', 'string', 'max:50'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        if (! empty($validated['parent_id'])) {
            if ($validated['parent_id'] === $academicUnit->id) {
                return ApiResponse::error(
                    'Academic unit cannot be its own parent.',
                    422,
                );
            }

            $parent = AcademicUnit::query()
                ->where('id', $validated['parent_id'])
                ->where('tenant_id', $organization->id)
                ->first();

            if ($parent === null) {
                return ApiResponse::error(
                    'Parent unit does not belong to this organization.',
                    422,
                );
            }
        }

        $academicUnit->update($validated);

        $this->auditLogger->log(
            AuditEventType::AcademicUnitUpdated->value,
            $academicUnit,
            $oldValues,
            $academicUnit->fresh()->toArray(),
            ['organization_id' => $organization->id],
        );

        return ApiResponse::success(
            new AcademicUnitResource($academicUnit->fresh()->load('parent', 'children')),
            'Academic unit updated successfully.',
        );
    }

    public function destroy(Request $request, Tenant $organization, AcademicUnit $academicUnit): JsonResponse
    {
        if ($academicUnit->tenant_id !== $organization->id) {
            return ApiResponse::error('Academic unit not found.', 404);
        }

        Gate::authorize('delete', $academicUnit);

        $oldValues = $academicUnit->toArray();

        $academicUnit->delete();

        $this->auditLogger->log(
            AuditEventType::AcademicUnitDeleted->value,
            $academicUnit,
            $oldValues,
            null,
            ['organization_id' => $organization->id],
        );

        return ApiResponse::success(
            null,
            'Academic unit deleted successfully.',
        );
    }
}
