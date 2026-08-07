<?php

namespace App\Http\Requests\Membership;

use App\Enums\MembershipStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by controller/middleware
    }

    public function rules(): array
    {
        return [
            'role_id' => ['sometimes', 'string', Rule::exists('roles', 'id')],
            'status' => ['sometimes', new Enum(MembershipStatus::class)],
        ];
    }
}
