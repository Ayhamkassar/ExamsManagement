<?php

namespace App\Http\Resources\Academic;

use App\Models\AcademicUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AcademicUnit */
class AcademicUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type->value,
            'name' => $this->name,
            'code' => $this->code,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'parent' => new self($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
