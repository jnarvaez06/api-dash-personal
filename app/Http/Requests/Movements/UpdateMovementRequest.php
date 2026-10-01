<?php

namespace App\Http\Requests\Movements;

use App\Enums\MovementType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovementRequest extends FormRequest
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
            'category_id' => [
                'sometimes',
                'required',
                Rule::exists('categories', 'id')->where('user_id', $this->user()?->id),
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'amount' => [
                'sometimes',
                'required',
                'numeric',
            ],
            'date' => [
                'sometimes',
                'required',
                'date',
            ],
            'type' => [
                'sometimes',
                'required',
                Rule::enum(MovementType::class),
            ],
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
            'category_id' => ['description' => 'Id de una categoría propia del usuario autenticado. Opcional.', 'example' => 1],
            'description' => ['description' => 'Descripción del movimiento. Opcional.', 'example' => 'Compra en el supermercado'],
            'amount' => ['description' => 'Monto del movimiento. Opcional.', 'example' => 150000],
            'date' => ['description' => 'Fecha del movimiento (YYYY-MM-DD). Opcional.', 'example' => '2026-08-20'],
            'type' => [
                'description' => 'Tipo de movimiento. Opcional. Valores válidos: '.implode(', ', array_column(MovementType::cases(), 'value')).'.',
                'example' => 'expense',
            ],
        ];
    }
}
