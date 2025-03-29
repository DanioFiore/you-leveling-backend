<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registers a new user in the system.
     *
     * This method validates the input data, creates a new user with the provided information,
     * and returns the user's name and email upon successful registration.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user registration data
     * @return \Illuminate\Http\JsonResponse JSON response with user data or validation errors
     * @throws \Illuminate\Validation\ValidationException When validation fails
     *
     * Required request parameters:
     * - name: The user's full name
     * - email: A valid email address
     * - password: The user's chosen password
     * - confirmPassword: Password confirmation (must match password)
     */
    public function register(Request $request): JsonResponse
    {
        return ApiResponse::handle(function() use ($request) {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required'],
                'confirmPassword' => ['required', 'same:password']
            ]);
    
            if($validator->fails()){
                throw new ValidationException($validator);
            }

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['name'] =  $user->name;
            $success['email'] = $user->email;
    
            return $success;
        });
    }
}
