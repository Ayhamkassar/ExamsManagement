<?php

namespace App\Http\Requests\Examination;

use Illuminate\Foundation\Http\FormRequest;

class StoreExaminationCycleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'ulid', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:examination_cycles,code,NULL,id,tenant_id,' . $this->route('organization')],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:school,university,national,institutional,certification,custom'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'An examination cycle with this code already exists in your organization.',
        ];
    }
}
