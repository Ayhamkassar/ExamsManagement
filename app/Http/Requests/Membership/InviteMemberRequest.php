<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'string', Rule::exists('roles', 'id')],
        ];
    }
}
