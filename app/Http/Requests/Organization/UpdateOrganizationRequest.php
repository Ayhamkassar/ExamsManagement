<?php

namespace App\Http\Requests\Organization;

use App\Enums\OrganizationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by controller/middleware
    }

    public function rules(): array
    {
        $tenant = $this->route('organization');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('tenants', 'slug')->ignoreModel($tenant)],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', new Enum(OrganizationType::class)],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
