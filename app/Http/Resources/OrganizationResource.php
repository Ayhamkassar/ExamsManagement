<?php

namespace App\Http\Resources;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tenant */
class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'legal_name' => $this->legal_name,
            'type' => optional($this->type)->value,
            'status' => optional($this->status)->value,
            'logo_path' => $this->logo_path,
            'settings' => $this->settings,
            'member_count' => $this->member_count ?? null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
