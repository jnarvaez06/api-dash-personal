<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Authentication', 'Registro, login, gestión de sesión y perfil del usuario autenticado.')]
class AuthController extends Controller
{
    #[Endpoint('Login', 'Autentica un usuario con email y contraseña y devuelve un token Sanctum.')]
    #[Unauthenticated]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'token' => '1|abcdef123456...',
            'user' => [
                'id' => 1,
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'profile' => ['country' => 'CO', 'currency' => 'COP'],
                'created_at' => '2026-01-10T10:00:00.000000Z',
            ],
        ],
    ])]
    #[Response(status: 401, content: [
        'success' => false,
        'message' => 'Invalid credentials',
    ], description: 'Credenciales inválidas o usuario inactivo.')]
    public function login(LoginRequest $request)
    {
        $user = User::query()
            ->where('email', $request->email)
            ->where('is_active', true)
            ->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }

    #[Endpoint('Register', 'Crea un nuevo usuario y su perfil, devuelve un token Sanctum.')]
    #[Unauthenticated]
    #[Response(status: 201, content: [
        'success' => true,
        'message' => 'User registered successfully',
        'data' => [
            'token' => '1|abcdef123456...',
            'user' => [
                'id' => 1,
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'profile' => ['country' => 'CO', 'currency' => 'COP'],
                'created_at' => '2026-01-10T10:00:00.000000Z',
            ],
        ],
    ])]
    #[Response(status: 422, content: [
        'success' => false,
        'message' => 'The given data was invalid.',
        'data' => null,
        'errors' => ['email' => ['The email has already been taken.']],
    ], description: 'Error de validación.')]
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);
        
        $user->profile()->create([
            'country' => $request->country ?? 'CO',
            'currency' => $request->currency ?? 'COP',
        ]);
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    #[Endpoint('Logout', 'Revoca el token de acceso actual.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Logged out successfully',
        'data' => null,
    ])]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
    }

    #[Endpoint('Get authenticated user', 'Devuelve el usuario autenticado junto a su perfil.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'User retrieved successfully',
        'data' => [
            'user' => [
                'id' => 1,
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'profile' => ['country' => 'CO', 'currency' => 'COP'],
                'created_at' => '2026-01-10T10:00:00.000000Z',
            ],
        ],
    ])]
    public function me(Request $request)
    {
        $user = $request->user()->load('profile');
        
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    #[Endpoint('Update profile', 'Actualiza los datos del usuario y/o su perfil. Todos los campos son opcionales.')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'User updated successfully',
        'data' => [
            'user' => [
                'id' => 1,
                'name' => 'Juan Pérez',
                'email' => 'nuevo@example.com',
                'profile' => ['country' => 'CO', 'currency' => 'COP'],
                'created_at' => '2026-01-10T10:00:00.000000Z',
            ],
        ],
    ])]
    public function updateMe(UpdateProfileRequest $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
        
            $userData = $request->safe()->only(['name', 'email']);

            $profileData = $request->safe()->only(['country', 'currency']);
            
            if (!empty($userData)) {
                $user->update($userData);
            }
            
            if (!empty($profileData)) {
                $user->profile()->update($profileData);
            }
        
        });

        $user->load('profile');
        
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    #[Endpoint('Change password', 'Cambia la contraseña del usuario y revoca todos sus tokens (requiere volver a hacer login).')]
    #[Response(status: 200, content: [
        'success' => true,
        'message' => 'Password changed successfully, please login again',
        'data' => null,
    ])]
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        
        $user->update([
            'password' => $request->new_password,
        ]);

        $user->tokens()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully, please login again',
            'data' => null,
        ]);
    }
}
