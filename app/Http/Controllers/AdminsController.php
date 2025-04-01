<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminsController extends Controller
{
    /**
     * Retrieve all admin users.
     *
     * This method fetches all users with admin privileges from the database.
     * Uses the ApiResponse::handle helper to standardize response format and handle exceptions.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing a list of admin users
     * @throws \Exception If an error occurs during the retrieval process
     */
    public function index(): JsonResponse
    {
        return ApiResponse::handle(function () {
            
            if (!Auth::user()->is_admin) {
                throw new Exception('You do not have permission to perform this action');
            }

            $admins = User::where('is_admin', true)->get();

            return $admins;
        });
    }

    /**
     * Update the admin status of a user.
     *
     * This method handles updating the admin privileges of a user in the system.
     * It requires the authenticated user to have admin privileges to perform this action.
     * 
     * @param  \Illuminate\Http\Request  $request  The HTTP request containing user ID and admin status
     * @throws \Illuminate\Validation\ValidationException  If validation fails
     * @throws \Exception  If the authenticated user is not an admin
     * @return \Illuminate\Http\JsonResponse  Response indicating success or failure
     */
    public function update(Request $request): JsonResponse
    {
        return ApiResponse::handle(function () use ($request) {

            $validator = Validator::make($request->all(), [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
                'is_admin' => ['bail', 'required', 'integer', 'in:0,1']
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if (!Auth::user()->is_admin) {
                throw new Exception('You do not have permission to perform this action');
            }

            $admin = User::findOrFail($request->id);
            $admin->is_admin = $request->is_admin;
            $admin->save();

            return 'Admin status updated successfully';
        });
    }
}
