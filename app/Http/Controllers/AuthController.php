<?php

namespace App\Http\Controllers;

use Exception;
use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'email' => ['required', 'string', 'email', 'unique:users,email'],
                'password' => ['required'],
                'confirmPassword' => ['required', 'same:password']
            ]);
    
            if ($validator->fails()){
                throw new ValidationException($validator);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken('registerToken')->plainTextToken;
            $success['name'] =  $user->name;
            $success['email'] = $user->email;
            $success['token'] = $token;
    
            return $success;
        });
    }

    /**
     * Handles user login authentication.
     *
     * This method validates the incoming login request, checks user credentials,
     * and generates an authentication token upon successful login.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user credentials
     * @return \Illuminate\Http\JsonResponse JSON response with user data and token or error message
     * @throws \Illuminate\Validation\ValidationException When validation fails
     * @throws \Exception When credentials are invalid
     */
    public function login(Request $request): JsonResponse
    {
        return ApiResponse::handle(function() use ($request) {

            $validator = Validator::make($request->all(), [
                'email'    => ['required', 'string', 'email', 'exists:users,email'],
                'password' => ['required', 'string']
            ]);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
            $user = User::where('email', $request->email)->first();
    
            // check if the user exists and the provided password is correct.
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid credentials');
            }
    
            // generate a token for the authenticated user.
            $token = $user->createToken('loginToken')->plainTextToken;
    
            // return the user data and token in the response
            return [
                'user'   => $user,
                'token'  => $token,
            ];
        });
    }

    public function logout(): JsonResponse
    {
        return ApiResponse::handle(function() {

            // Retrieve the authenticated user from the request.
            $user = Auth::user();

            if (!$user) {
                throw new Exception('User not authenticated');
            }
    
            // revoke the current access token.
            $user->currentAccessToken()->delete();
    
            return 'Logged out successfully';
        });
    }
}
