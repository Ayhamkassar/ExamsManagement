<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\InviteMemberRequest;
use App\Http\Requests\Membership\UpdateMembershipRequest;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\SecurityEventLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MembershipController extends Controller
{
    public function __construct(
        private readonly SecurityEventLogger $securityEventLogger,
    ) {}

    public function index(Request $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('manageMembers', $organization);

        $memberships = $organization->memberships()
            ->with(['user', 'role'])
            ->paginate();

        return ApiResponse::success(
            MembershipResource::collection($memberships),
        );
    }

    public function invite(InviteMemberRequest $request, Tenant $organization): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('manageMembers', $organization);

        $validated = $request->validated();

        // Check if user exists
        $invitedUser = User::where('email', $validated['email'])->first();

        if ($invitedUser) {
            // Check if already a member
            $existing = Membership::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $invitedUser->id)
                ->first();

            if ($existing) {
                return ApiResponse::error(
                    'User is already a member of this organization.',
                    409,
                );
            }

            // Create active membership immediately
            $membership = Membership::create([
                'organization_id' => $organization->id,
                'user_id' => $invitedUser->id,
                'role_id' => $validated['role_id'],
                'status' => MembershipStatus::Active,
                'joined_at' => now(),
                'invited_by' => $user->id,
            ]);

            $this->securityEventLogger->record(
                'organization.member.joined',
                $user,
                [
                    'organization_id' => $organization->id,
                    'member_user_id' => $invitedUser->id,
                    'role_id' => $validated['role_id'],
                ],
            );
        } else {
            // Future: Send invitation email
            // For now, create invited membership
            $membership = Membership::create([
                'organization_id' => $organization->id,
                'user_id' => null, // Will be set when user accepts
                'role_id' => $validated['role_id'],
                'status' => MembershipStatus::Invited,
                'invited_by' => $user->id,
            ]);

            $this->securityEventLogger->record(
                'organization.member.invited',
                $user,
                [
                    'organization_id' => $organization->id,
                    'email' => $validated['email'],
                    'role_id' => $validated['role_id'],
                ],
            );
        }

        return ApiResponse::created(
            new MembershipResource($membership->load(['user', 'role', 'invitedBy'])),
            'Member invited successfully.',
        );
    }

    public function update(UpdateMembershipRequest $request, Tenant $organization, Membership $membership): JsonResponse
    {
        $user = $request->user();

        if ($membership->organization_id !== $organization->id) {
            return ApiResponse::error(
                'Membership does not belong to this organization.',
                404,
            );
        }

        Gate::authorize('update', $membership);

        $oldStatus = $membership->status;
        $oldRoleId = $membership->role_id;

        $membership->update($request->validated());

        // Log role changes
        if ($request->has('role_id') && $oldRoleId !== $membership->role_id) {
            $this->securityEventLogger->record(
                'organization.member.role_changed',
                $user,
                [
                    'organization_id' => $organization->id,
                    'membership_id' => $membership->id,
                    'member_user_id' => $membership->user_id,
                    'old_role_id' => $oldRoleId,
                    'new_role_id' => $membership->role_id,
                ],
            );
        }

        // Log status changes
        if ($request->has('status') && $oldStatus !== $membership->status) {
            $oldStatusValue = (string) $oldStatus;
            $newStatusValue = (string) $membership->status;

            $this->securityEventLogger->record(
                'organization.member.status_changed',
                $user,
                [
                    'organization_id' => $organization->id,
                    'membership_id' => $membership->id,
                    'member_user_id' => $membership->user_id,
                    'old_status' => $oldStatusValue,
                    'new_status' => $newStatusValue,
                ],
            );
        }

        return ApiResponse::success(
            new MembershipResource($membership->load(['user', 'role', 'invitedBy'])),
            message: 'Membership updated successfully.',
        );
    }

    public function destroy(Request $request, Tenant $organization, Membership $membership): JsonResponse
    {
        $user = $request->user();

        if ($membership->organization_id !== $organization->id) {
            return ApiResponse::error(
                'Membership does not belong to this organization.',
                404,
            );
        }

        Gate::authorize('delete', $membership);

        $this->securityEventLogger->record(
            'organization.member.removed',
            $user,
            [
                'organization_id' => $organization->id,
                'membership_id' => $membership->id,
                'removed_user_id' => $membership->user_id,
            ],
        );

        $membership->delete();

        return ApiResponse::success(
            null,
            'Member removed successfully.',
        );
    }
}
