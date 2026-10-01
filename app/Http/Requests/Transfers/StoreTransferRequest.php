<?php

namespace App\Http\Requests\Transfers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()?->id)
                    ->where('is_active', true),
            ],
            'to_account_id' => [
                'required',
                'integer',
                'different:from_account_id',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()?->id)
                    ->where('is_active', true),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
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
            'from_account_id' => ['description' => 'Id de la cuenta activa propia de origen.', 'example' => 1],
            'to_account_id' => ['description' => 'Id de la cuenta activa propia de destino. Debe ser distinta de from_account_id.', 'example' => 2],
            'amount' => ['description' => 'Monto a transferir (mayor a 0).', 'example' => 200000],
            'date' => ['description' => 'Fecha de la transferencia (YYYY-MM-DD).', 'example' => '2026-09-30'],
            'description' => ['description' => 'Descripción opcional. Si se omite, se genera una por defecto con los nombres de las cuentas.', 'example' => 'Pago tarjeta de crédito'],
        ];
    }
}
