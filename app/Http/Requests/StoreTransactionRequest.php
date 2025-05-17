<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payer_id' => 'required|exists:users,id',
            'payee_id' => 'required|exists:users,id',
            'value' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $payer = User::findOrFail($this->input('payer_id'));
            if ($payer->type !== 'customer') {
                $validator->errors()->add('payer', 'Logistas não podem enviar dinheiro.');
            }
        });
    }
}
