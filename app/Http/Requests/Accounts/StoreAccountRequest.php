<?php

namespace App\Http\Requests\Accounts;

use App\Enums\AccountType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::enum(AccountType::class)],
            'initial_balance' => 'required|numeric',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the descriptions/examples of the request's body parameters for API docs.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nombre de la cuenta.', 'example' => 'Cuenta de ahorros'],
            'type' => [
                'description' => 'Tipo de cuenta. Valores válidos: '.implode(', ', array_column(AccountType::cases(), 'value')).'.',
                'example' => 'bank_account',
            ],
            'initial_balance' => ['description' => 'Saldo inicial de la cuenta.', 'example' => 1500000],
            'is_active' => ['description' => 'Si la cuenta está activa. Opcional, default `true`.', 'example' => true],
        ];
    }
}
