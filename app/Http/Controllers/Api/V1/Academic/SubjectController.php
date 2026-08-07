<?php

namespace App\Http\Controllers\Api\V1\Academic;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Academic\SubjectResource;
use App\Models\Subject;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubjectController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('viewAny', Subject::class);

        $subjects = Subject::query()
            ->where('tenant_id', $organization->id)
            ->paginate();

        return ApiResponse::success(
            SubjectResource::collection($subjects),
        );
    }

    public function store(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('create', Subject::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $validated['tenant_id'] = $organization->id;

        $subject = Subject::create($validated);

        $this->auditLogger->log(
            AuditEventType::SubjectCreated->value,
            $subject,
            null,
            $subject->toArray(),
            [
                'organization_id' => $organization->id,
            ],
        );

        $this->securityEventLogger->record(
            'subject.created',
            $user,
            [
                'organization_id' => $organization->id,
                'subject_id' => $subject->id,
                'name' => $subject->name,
            ],
        );

        return ApiResponse::created(
            new SubjectResource($subject),
            'Subject created successfully.',
        );
    }

    public function show(Request $request, Tenant $organization, Subject $subject): JsonResponse
    {
        $user = $request->user();

        if ($subject->tenant_id !== $organization->id) {
            return ApiResponse::error('Subject not found.', 404);
        }

        Gate::authorize('view', $subject);

        return ApiResponse::success(
            new SubjectResource($subject),
        );
    }

    public function update(Request $request, Tenant $organization, Subject $subject): JsonResponse
    {
        $user = $request->user();

        if ($subject->tenant_id !== $organization->id) {
            return ApiResponse::error('Subject not found.', 404);
        }

        Gate::authorize('update', $subject);

        $oldValues = $subject->toArray();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        $subject->update($validated);

        $this->auditLogger->log(
            AuditEventType::SubjectUpdated->value,
            $subject,
            $oldValues,
            $subject->fresh()->toArray(),
            [
                'organization_id' => $organization->id,
            ],
        );

        return ApiResponse::success(
            new SubjectResource($subject->fresh()),
            'Subject updated successfully.',
        );
    }

    public function destroy(Request $request, Tenant $organization, Subject $subject): JsonResponse
    {
        $user = $request->user();

        if ($subject->tenant_id !== $organization->id) {
            return ApiResponse::error('Subject not found.', 404);
        }

        Gate::authorize('delete', $subject);

        $oldValues = $subject->toArray();

        $subject->delete();

        $this->auditLogger->log(
            AuditEventType::SubjectDeleted->value,
            $subject,
            $oldValues,
            null,
            [
                'organization_id' => $organization->id,
            ],
        );

        return ApiResponse::success(
            null,
            'Subject deleted successfully.',
        );
    }
}
