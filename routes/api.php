<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestsController;
use App\Http\Controllers\UsersController;

// USE THIS ROUTE FOR TESTING
Route::get('/test', [TestsController::class, 'test']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Apply sanctum authentication middleware to a group of routes
Route::middleware('auth:sanctum')->group(function () {
    // AUTH
    Route::post('/logout', [AuthController::class, 'logout']);

    // USERS
    Route::get('/users', [UsersController::class, 'index']);
    Route::get('/users/{id}', [UsersController::class, 'show']);
    Route::patch('/users', [UsersController::class, 'update']);
    Route::patch('/users/{id}/restore', [UsersController::class, 'restore']);
    Route::delete('/users/{id}', [UsersController::class, 'softDestroy']);

    // ADMINS
    Route::get('/admins', [AdminsController::class, 'index']);
    Route::patch('/admins/{id}', [AdminsController::class, 'update']);
});