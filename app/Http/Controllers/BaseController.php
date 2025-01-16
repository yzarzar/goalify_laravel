<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class BaseController
 *
 * A foundation controller that provides standardized API response methods
 * and error handling capabilities for the application.
 *
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{
    /**
     * HTTP Status Codes
     */
    protected const HTTP_OK = ResponseAlias::HTTP_OK;                                       // 200
    protected const HTTP_CREATED = ResponseAlias::HTTP_CREATED;                             // 201
    protected const HTTP_NO_CONTENT = ResponseAlias::HTTP_NO_CONTENT;                       // 204
    protected const HTTP_BAD_REQUEST = ResponseAlias::HTTP_BAD_REQUEST;                     // 400
    protected const HTTP_UNAUTHORIZED = ResponseAlias::HTTP_UNAUTHORIZED;                   // 401
    protected const HTTP_FORBIDDEN = ResponseAlias::HTTP_FORBIDDEN;                         // 403
    protected const HTTP_NOT_FOUND = ResponseAlias::HTTP_NOT_FOUND;                         // 404
    protected const HTTP_METHOD_NOT_ALLOWED = ResponseAlias::HTTP_METHOD_NOT_ALLOWED;       // 405
    protected const HTTP_UNPROCESSABLE_ENTITY = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;   // 422
    protected const HTTP_TOO_MANY_REQUESTS = ResponseAlias::HTTP_TOO_MANY_REQUESTS;         // 429
    protected const HTTP_SERVER_ERROR = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;          // 500
    protected const HTTP_SERVICE_UNAVAILABLE = ResponseAlias::HTTP_SERVICE_UNAVAILABLE;     // 503

    /**
     * Response Status Messages
     */
    protected const STATUS_SUCCESS = 'success';
    protected const STATUS_ERROR = 'error';
    protected const STATUS_WARNING = 'warning';
    protected const STATUS_INFO = 'info';

    /**
     * Default Messages
     */
    protected const DEFAULT_SUCCESS_MESSAGE = 'Operation completed successfully';
    protected const DEFAULT_ERROR_MESSAGE = 'An error occurred while processing your request';
    protected const DEFAULT_CREATED_MESSAGE = 'Resource created successfully';
    protected const DEFAULT_UPDATED_MESSAGE = 'Resource updated successfully';
    protected const DEFAULT_DELETED_MESSAGE = 'Resource deleted successfully';

    /**
     * Send a success response.
     *
     * @param mixed $data Data to be returned
     * @param string|null $message Success message
     * @param int $code HTTP status code
     * @param array $headers Additional headers
     * @return JsonResponse
     */
    protected function sendSuccess(
        mixed $data = null,
        ?string $message = null,
        int $code = self::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return $this->apiResponse(
            true,
            $message ?? self::DEFAULT_SUCCESS_MESSAGE,
            $data,
            [],
            $code,
            $headers
        );
    }

    /**
     * Send a collection response with pagination support.
     *
     * @param Collection|JsonResource $data
     * @param string|null $message
     * @param int $code
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendCollection(
        Collection|JsonResource $data,
        ?string $message = null,
        int $code = self::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $response = $data instanceof JsonResource ? $data->response()->getData(true) : $data;

        return $this->apiResponse(
            true,
            $message ?? self::DEFAULT_SUCCESS_MESSAGE,
            $response,
            [],
            $code,
            $headers
        );
    }

    /**
     * Send an error response.
     *
     * @param string|null $message Error message
     * @param array $errors Detailed error messages
     * @param int $code HTTP status code
     * @param array $headers Additional headers
     * @param \Throwable|null $exception Exception if any
     * @return JsonResponse
     */
    protected function sendError(
        ?string $message = null,
        array $errors = [],
        int $code = self::HTTP_BAD_REQUEST,
        array $headers = [],
        ?\Throwable $exception = null
    ): JsonResponse {
        if ($exception) {
            Log::error($exception->getMessage(), [
                'exception' => $exception,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        return $this->apiResponse(
            false,
            $message ?? self::DEFAULT_ERROR_MESSAGE,
            null,
            $errors,
            $code,
            $headers
        );
    }

    /**
     * Send a created response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendCreated(
        mixed $data = null,
        ?string $message = null,
        array $headers = [],
    ): JsonResponse {
        return $this->sendSuccess(
            $data,
            $message ?? self::DEFAULT_CREATED_MESSAGE,
            self::HTTP_CREATED,
            $headers
        );
    }

    /**
     * Send a no content response.
     *
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendNoContent(array $headers = []): JsonResponse
    {
        return $this->apiResponse(
            true,
            null,
            null,
            [],
            self::HTTP_NO_CONTENT,
            $headers
        );
    }

    /**
     * Send an unauthorized response.
     *
     * @param string|null $message
     * @param array $errors
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendUnauthorized(
        ?string $message = 'Unauthorized access',
        array $errors = [],
        array $headers = []
    ): JsonResponse {
        return $this->sendError($message, $errors, self::HTTP_UNAUTHORIZED, $headers);
    }

    /**
     * Send a forbidden response.
     *
     * @param string|null $message
     * @param array $errors
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendForbidden(
        ?string $message = 'Access forbidden',
        array $errors = [],
        array $headers = []
    ): JsonResponse {
        return $this->sendError($message, $errors, self::HTTP_FORBIDDEN, $headers);
    }

    /**
     * Send a not found response.
     *
     * @param string|null $message
     * @param array $errors
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendNotFound(
        ?string $message = 'Resource not found',
        array $errors = [],
        array $headers = []
    ): JsonResponse {
        return $this->sendError($message, $errors, self::HTTP_NOT_FOUND, $headers);
    }

    /**
     * Send a validation error response.
     *
     * @param array $errors
     * @param string|null $message
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendValidationError(
        array $errors,
        ?string $message = 'Validation failed',
        array $headers = []
    ): JsonResponse {
        return $this->sendError(
            $message,
            $errors,
            self::HTTP_UNPROCESSABLE_ENTITY,
            $headers
        );
    }

    /**
     * Send a too many requests error response.
     *
     * @param string|null $message
     * @param array $errors
     * @param array $headers
     * @return JsonResponse
     */
    protected function sendTooManyRequests(
        ?string $message = 'Too many requests',
        array $errors = [],
        array $headers = []
    ): JsonResponse {
        return $this->sendError($message, $errors, self::HTTP_TOO_MANY_REQUESTS, $headers);
    }

    /**
     * Send a server error response.
     *
     * @param string|null $message
     * @param array $errors
     * @param array $headers
     * @param \Throwable|null $exception
     * @return JsonResponse
     */
    protected function sendServerError(
        ?string $message = 'Server error',
        array $errors = [],
        array $headers = [],
        ?\Throwable $exception = null
    ): JsonResponse {
        return $this->sendError(
            $message,
            $errors,
            self::HTTP_SERVER_ERROR,
            $headers,
            $exception
        );
    }

    /**
     * Core API response method.
     *
     * @param bool $status
     * @param string|null $message
     * @param mixed|null $data
     * @param array $errors
     * @param int $code
     * @param array $headers
     * @return JsonResponse
     */
    protected function apiResponse(
        bool $status,
        ?string $message,
        mixed $data = null,
        array $errors = [],
        int $code = self::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => $status,
            'message' => $message,
            'status_code' => $code
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        // Add API version and timestamp
        $response['meta'] = [
            'api_version' => config('app.api_version', '1.0'),
            'timestamp' => now()->toIso8601String()
        ];

        return response()->json($response, $code, $headers);
    }
}
