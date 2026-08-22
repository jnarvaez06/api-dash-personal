<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'country' => ['sometimes', 'required', 'string', 'max:2'],
            'currency' => ['sometimes', 'required', 'string', 'max:3'],
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
            'name' => ['description' => 'Nombre completo. Opcional.', 'example' => 'Juan Pérez'],
            'email' => ['description' => 'Correo. Opcional, debe ser único.', 'example' => 'nuevo@example.com'],
            'country' => ['description' => 'Código de país ISO de 2 letras. Opcional.', 'example' => 'CO'],
            'currency' => ['description' => 'Código de moneda ISO de 3 letras. Opcional.', 'example' => 'COP'],
        ];
    }
}
