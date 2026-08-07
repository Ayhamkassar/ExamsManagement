<?php

namespace App\Http\Requests\Examination;

use Illuminate\Foundation\Http\FormRequest;

class StoreExaminationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'examination_cycle_id' => ['required', 'ulid', 'exists:examination_cycles,id'],
            'subject_id' => ['required', 'ulid', 'exists:subjects,id'],
            'academic_unit_id' => ['nullable', 'ulid', 'exists:academic_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:examinations,code,NULL,id,tenant_id,' . $this->route('organization') . ',examination_cycle_id,' . $this->input('examination_cycle_id')],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'total_marks' => ['required', 'integer', 'min:1'],
            'passing_marks' => ['nullable', 'integer', 'lte:total_marks'],
            'configuration' => ['nullable', 'array'],
        ];
    }
}
