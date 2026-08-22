<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CategoryController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateMe']);
    Route::patch('/me/password', [AuthController::class, 'changePassword']);    
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('categories', CategoryController::class);

});
