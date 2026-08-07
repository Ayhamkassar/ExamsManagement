<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('roles', 'slug')->where(fn ($query) => $query->where('tenant_id', $this->input('tenant_id'))),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'tenant_id' => ['nullable', 'string', 'exists:tenants,id'],
        ];
    }
}
