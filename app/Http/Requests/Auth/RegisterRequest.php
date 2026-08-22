<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'nullable|string|max:2',
            'currency' => 'nullable|string|max:3',
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
            'name' => ['description' => 'Nombre completo del usuario.', 'example' => 'Juan Pérez'],
            'email' => ['description' => 'Correo único del usuario.', 'example' => 'juan@example.com'],
            'password' => ['description' => 'Contraseña, mínimo 8 caracteres.', 'example' => 'password123'],
            'password_confirmation' => ['description' => 'Confirmación de la contraseña (debe coincidir con `password`).', 'example' => 'password123'],
            'country' => ['description' => 'Código de país ISO de 2 letras. Opcional, default `CO`.', 'example' => 'CO'],
            'currency' => ['description' => 'Código de moneda ISO de 3 letras. Opcional, default `COP`.', 'example' => 'COP'],
        ];
    }
}
