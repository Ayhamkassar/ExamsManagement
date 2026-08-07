<?php

namespace App\Http\Requests\Academic;

use App\Enums\AcademicUnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAcademicUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'ulid', 'exists:academic_units,id'],
            'type' => ['sometimes', new Enum(AcademicUnitType::class)],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
