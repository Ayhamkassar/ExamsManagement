<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'signature' => ['required', 'string'],
            'expires' => ['required', 'integer'],
        ];
    }

    public function hasValidSignatureWrapper(): bool
    {
        return URL::hasValidSignature($this);
    }

    public function userMatchesHash(): bool
    {
        $user = User::query()->find($this->query('id'));

        if ($user === null) {
            return false;
        }

        return hash_equals(
            (string) $this->query('hash'),
            sha1($user->getEmailForVerification()),
        );
    }
}
