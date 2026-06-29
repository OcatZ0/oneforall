<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Format email tidak valid. Pastikan email mengandung domain yang lengkap (contoh: user@example.com).',
        ];
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('user', 'username')->ignore($id, 'id')],
            'email'    => ['required', 'email:filter', Rule::unique('user', 'email')->ignore($id, 'id')],
            'role'     => 'required|in:admin,customer',
            'agents'   => 'array',
            'agents.*' => 'string',
        ];
    }
}
