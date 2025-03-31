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
});

// TODO: create a middleware for admin users
// Route::delete('/users', [UsersController::class, 'destroy']);