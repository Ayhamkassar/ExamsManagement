<?php

namespace App\Http\Requests\Academic;

use App\Enums\AcademicYearStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date', 'before:end_date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', new Enum(AcademicYearStatus::class)],
        ];
    }
}
