<?php

namespace App\Http\Requests\Examination;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExaminationCycleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', 'unique:examination_cycles,code,' . $this->route('examinationCycle')->id . ',id,tenant_id,' . $this->route('organization')],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', 'in:school,university,national,institutional,certification,custom'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
