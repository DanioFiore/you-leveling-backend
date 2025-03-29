<?php

namespace App;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiResponse
{
    /**
     * Handles API responses with a standardized format and error handling.
     *
     * This method wraps the execution of a callback function in a try-catch block to
     * provide consistent error handling for API responses. It handles different types
     * of exceptions and formats responses accordingly.
     *
     * @param callable $callback The function to execute and return its result as a JSON response
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with standardized structure:
     *         - On success: ['status' => 'success', 'data' => result] with 200 status code
     *         - On validation error: ['status' => 'error', 'message' => '...', 'errors' => [...]] with 422 status code
     *         - On resource not found: ['status' => 'error', 'message' => 'Resource not found'] with 404 status code
     *         - On other exceptions: ['status' => 'error', 'message' => '...'] with 500 status code
     *
     * @throws \Exception If an unexpected error occurs that isn't caught by the internal try-catch
     *
     * @note In debug mode or non-production environments, additional error details are included in responses
     */
    public static function handle(callable $callback)
    {
        try {
            // execute the callback function and return the result as a JSON response
            $result = $callback();
            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            Log::warning('Validation Error: ' . $e->getMessage());

            if (config('app.debug') || config('app.env') === 'local' || config('app.env') === 'testing') {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            Log::error('Resource not found: ' . $e->getMessage());

            if (config('app.debug') || config('app.env') === 'local' || config('app.env') === 'testing') {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Internal error: ' . $e->getMessage());

            if (config('app.debug') || config('app.env') === 'local' || config('app.env') === 'testing') {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'An internal error occurred',
            ], 500);
        }
    }
}
