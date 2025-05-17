<?php

namespace App\Http\Requests\User;

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
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,shopkeeper',
            'cpf_cnpj' => 'required|string|cpf_ou_cnpj|unique:users,cpf_cnpj',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:10|confirmed',
        ];
    }
}
