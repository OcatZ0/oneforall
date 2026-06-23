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
            'email'    => 'required|email|unique:user,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,customer',
            'agents'   => 'array',
            'agents.*' => 'string',
        ];
    }
}
