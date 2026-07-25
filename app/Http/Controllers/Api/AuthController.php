<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', '=', $request->email)
            ->where('is_active', '=', true)
            ->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
