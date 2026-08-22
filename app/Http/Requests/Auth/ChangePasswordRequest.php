<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
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
            'current_password' => ['description' => 'Contraseña actual del usuario.', 'example' => 'oldpassword123'],
            'new_password' => ['description' => 'Nueva contraseña, mínimo 8 caracteres y diferente de la actual.', 'example' => 'newpassword456'],
            'new_password_confirmation' => ['description' => 'Confirmación de la nueva contraseña.', 'example' => 'newpassword456'],
        ];
    }
}
