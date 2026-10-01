<?php

namespace App\Http\Requests\Transfers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransferRequest extends FormRequest
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
            'from_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()?->id)
                    ->where('is_active', true),
            ],
            'to_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()?->id)
                    ->where('is_active', true),
            ],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'date' => ['sometimes', 'required', 'date'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
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
            'from_account_id' => ['description' => 'Id de la cuenta activa propia de origen. Opcional.', 'example' => 1],
            'to_account_id' => ['description' => 'Id de la cuenta activa propia de destino. Opcional. Debe terminar siendo distinta de from_account_id.', 'example' => 2],
            'amount' => ['description' => 'Monto a transferir (mayor a 0). Opcional.', 'example' => 250000],
            'date' => ['description' => 'Fecha de la transferencia (YYYY-MM-DD). Opcional.', 'example' => '2026-09-30'],
            'description' => ['description' => 'Descripción. Opcional.', 'example' => 'Pago tarjeta de crédito (actualizada)'],
        ];
    }
}
