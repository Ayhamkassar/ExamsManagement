<?php

namespace App\Http\Requests\Examination;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExaminationRequest extends FormRequest
{
    public function rules(): array
    {
        $examination = $this->route('examination');

        return [
            'subject_id' => ['sometimes', 'ulid', 'exists:subjects,id'],
            'academic_unit_id' => ['nullable', 'ulid', 'exists:academic_units,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', 'unique:examinations,code,' . $examination->id . ',id,tenant_id,' . $this->route('organization') . ',examination_cycle_id,' . $examination->examination_cycle_id],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'total_marks' => ['sometimes', 'integer', 'min:1'],
            'passing_marks' => ['nullable', 'integer', 'lte:total_marks'],
            'configuration' => ['nullable', 'array'],
        ];
    }
}
