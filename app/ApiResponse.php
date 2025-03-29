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
            // execute the callback function
            $result = $callback();

            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            Log::warning('Validation Error: ' . $e->getMessage());

            return self::errorResponse($e, 'Validation Error', 422);
        } catch (ModelNotFoundException $e) {
            Log::error('Resource not found: ' . $e->getMessage());

            return self::errorResponse($e, 'Resource not found', 404);
        } catch (Exception $e) {
            Log::error('Internal error: ' . $e->getMessage());

            return self::errorResponse($e, 'An internal error occurred', 500);
        }
    }


    /**
     * Generate an error response based on an exception
     *
     * This method creates a JSON response for error handling with appropriate data
     * based on the application environment.
     *
     * In debug mode (local or testing environments), it includes detailed error information
     * such as the error message, file, and line number.
     *
     * In production, it shows only a generic message and validation errors if available.
     *
     * @param Exception $e The exception that was thrown
     * @param string $genericMessage A user-friendly message for production environments
     * @param int $statusCode The HTTP status code to return
     * @return \Illuminate\Http\JsonResponse The formatted JSON response
     */
    private static function errorResponse(Exception $e, string $genericMessage, int $statusCode)
    {
        $debug = config('app.debug') || in_array(config('app.env'), ['local', 'testing']);
        
        if ($debug) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], $statusCode);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => $genericMessage,
            // if is a validation exception, include the errors
            'errors' => method_exists($e, 'errors') ? $e->errors() : null
        ], $statusCode);
    }

}
