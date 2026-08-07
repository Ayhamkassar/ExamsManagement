<?php

namespace App\Http\Resources;

use App\Models\Membership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Membership */
class MembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'role' => $this->whenLoaded('role', function () {
                /** @var Role $role */
                $role = $this->role;

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ];
            }),
            'status' => optional($this->status)->value,
            'joined_at' => optional($this->joined_at)->toISOString(),
            'invited_by' => $this->whenLoaded('invitedBy', function () {
                /** @var User $user */
                $user = $this->invitedBy;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
