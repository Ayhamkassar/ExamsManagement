<?php

namespace App\Http\Requests\Academic;

use App\Enums\AcademicYearStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'before:end_date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', new Enum(AcademicYearStatus::class)],
        ];
    }
}
