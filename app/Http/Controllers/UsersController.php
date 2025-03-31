<?php

namespace App\Http\Controllers;

use Exception;
use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    /**
     * Retrieve all users.
     *
     * This method fetches all users from the database and returns them
     * in a JSON response using the ApiResponse handler.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing all users data
     */
    public function index(): JsonResponse
    {
        return ApiResponse::handle(function () {
            
            return User::all();
        });
    }

    /**
     * Display the specified user.
     * 
     * This method retrieves a specific user by ID. It performs validation to ensure
     * the ID is valid and exists in the database. It also checks that the authenticated
     * user is only viewing their own profile for security purposes.
     *
     * @param  int  $id  The ID of the user to retrieve
     * @return \Illuminate\Http\JsonResponse A JSON response containing the user data
     * @throws \Illuminate\Validation\ValidationException If validation fails
     * @throws \Exception If the authenticated user tries to view another user's profile
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            if ($id !== Auth::user()->id || Auth::user()->is_admin) {
                throw new Exception('You cannot view other users');
            }

            $user = User::findOrFail($id);

            return $user;
        });
    }

    /**
     * Update a user's information.
     *
     * This method allows a user to update their name and/or email.
     * It validates the request, ensures users can only update their own profile,
     * and then applies the changes to the database.
     *
     * @param  \Illuminate\Http\Request  $request  The request object containing 
     *                                             'id' (required),
     *                                             'name' (optional),
     *                                             'email' (optional)
     * @return \Illuminate\Http\JsonResponse       JSON response with success message or error details
     * @throws \Illuminate\Validation\ValidationException  When validation fails
     * @throws \Exception  When a user attempts to update another user's profile or no fields to update are provided
     */
    public function update(Request $request): JsonResponse
    {
        return ApiResponse::handle(function () use ($request) {

            $validator = Validator::make($request->all(), [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
                'name' => ['bail', 'nullable', 'string', 'max:255'],
                'email' => ['bail', 'nullable', 'string', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($request->input('id') !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot update other users');
            }

            if (!$request->has('name') && !$request->has('email')) {
                throw new Exception('No fields to update');
            }

            $user = User::find($request->input('id'));
            
            if ($request->has('name')) {
                $user->name = $request->input('name');
            }

            if ($request->has('email')) {
                $user->email = $request->input('email');
            }

            $user->save();

            return 'User updated successfully';
        });
    }

    /**
     * Soft deletes a user.
     * 
     * This method handles the soft deletion of a user identified by their ID. It performs the following steps:
     * 1. Validates that the given ID is a valid user ID in the database
     * 2. Checks that the authenticated user has permission to delete the user (must be admin or the same user)
     * 3. Finds the user and soft deletes it
     * 
     * @param int $id The ID of the user to be soft deleted
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure
     * @throws \Illuminate\Validation\ValidationException When validation fails
     * @throws \Exception When user tries to delete another user without admin privileges
     */
    public function softDestroy(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot delete other users');
            }

            $user = User::findOrFail($id);
            $user->delete();

            return 'User soft-deleted successfully';
        });
    }

    /**
     * Restores a soft-deleted user.
     * 
     * This method handles the restoration of a soft-deleted user identified by their ID. It performs the following steps:
     * 1. Validates that the given ID is a valid user ID in the database
     * 2. Checks that the authenticated user has permission to restore the user (must be admin or the same user)
     * 3. Finds the user and restores it
     * 
     * @param int $id The ID of the user to be restored
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure
     * @throws \Illuminate\Validation\ValidationException When validation fails
     * @throws \Exception When user tries to restore another user without admin privileges
     */
    public function restore(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id) {
                throw new Exception('You cannot restore other users');
            }

            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            return 'User restored successfully';
        });
    }

    /**
     * Permanently deletes a user.
     * 
     * This method handles the permanent deletion of a user identified by their ID. It performs the following steps:
     * 1. Validates that the given ID is a valid user ID in the database
     * 2. Checks that the authenticated user has permission to delete the user (must be admin or the same user)
     * 3. Finds the user and permanently deletes it
     * 
     * @param int $id The ID of the user to be permanently deleted
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure
     * @throws \Illuminate\Validation\ValidationException When validation fails
     * @throws \Exception When user tries to delete another user without admin privileges
     */
    public function destroy(int $id): JsonResponse
    {
        return ApiResponse::handle(function () use ($id) {

            $validator = Validator::make(['id' => $id], [
                'id' => ['bail', 'required', 'integer', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($id !== Auth::user()->id && !Auth::user()->is_admin) {
                throw new Exception('You cannot delete other users');
            }

            $user = User::withTrashed()->findOrFail($id);
            $user->forceDelete();

            return 'User permanently deleted successfully';
        });
    }
}
