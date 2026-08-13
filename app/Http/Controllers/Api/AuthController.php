<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
    }

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
}