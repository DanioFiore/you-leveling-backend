<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestsController;

// USE THIS ROUTE FOR TESTING
Route::get('/test', [TestsController::class, 'test']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Apply sanctum authentication middleware to a group of routes
Route::middleware('auth:sanctum')->group(function () {
    
});