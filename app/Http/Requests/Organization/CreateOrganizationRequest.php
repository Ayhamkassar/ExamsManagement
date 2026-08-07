<?php

namespace App\Http\Requests\Organization;

use App\Enums\OrganizationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', new Enum(OrganizationType::class)],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
