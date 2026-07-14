<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:50|unique:user,username',
            'email'    => 'required|email:filter|unique:user,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,customer',
            'agents'   => ['array', function ($attribute, $value, $fail) {
                if ($this->input('role') === 'admin' && !empty($value)) {
                    $fail('Admin tidak dapat ditugaskan agent.');
                }
            }],
            'agents.*' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Format email tidak valid. Pastikan email mengandung domain yang lengkap (contoh: user@example.com).',
        ];
    }
}
