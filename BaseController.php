<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;

class BaseController extends Controller
{
    protected string $successMessage = ' Success';
    protected string $errorMessage = ' Error';
    protected string $createdMessage = ' Created Successfully';
    protected string $updatedMessage = ' Updated Successfully';
    protected string $deletedMessage = ' Deleted Successfully';


    protected function respondSuccess($message = ''): JsonResponse
    {
        return $this->apiResponse(['success' => true, 'message' => $message]);
    }

    protected function apiResponse($data = [], $statusCode = 200, $headers = []): JsonResponse
    {
        $result = $this->parseGivenData($data, $statusCode, $headers);
        return response()->json(
            $result['content'], $result['statusCode'], $result['headers']
        );
    }

    public function parseGivenData($data = [], $statusCode = 200, $headers = []): array
    {
        $responseStructure = [
            'success' => $data['success'],
            'message' => $data['message'] ?? null,
            'result' => $data['result'] ?? null,
        ];
        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }
        if (isset($data['status'])) {
            $statusCode = $data['status'];
        }


        if (isset($data['exception']) && ($data['exception'] instanceof Error || $data['exception'] instanceof Exception)) {
            if (config('app.env') !== 'production') {
                $responseStructure['exception'] = [
                    'message' => $data['exception']->getMessage(),
                    'file' => $data['exception']->getFile(),
                    'line' => $data['exception']->getLine(),
                    'code' => $data['exception']->getCode(),
                    'trace' => $data['exception']->getTrace(),
                ];
            }

            if ($statusCode === 200) {
                $statusCode = 500;
            }
        }
        if ($data['success'] === false) {
            if (isset($data['error_code'])) {
                $responseStructure['error_code'] = $data['error_code'];
            } else {
                $responseStructure['error_code'] = 1;
            }
        }
        return ["content" => $responseStructure, "statusCode" => $statusCode, "headers" => $headers];
    }

    protected function respondCreated($data): JsonResponse
    {
        return $this->apiResponse($data, 201);
    }

    protected function respondFetched($data): JsonResponse
    {
        return $this->apiResponse($data, 201);
    }

    protected function respondNoContent($message = 'No Content Found'): JsonResponse
    {
        return $this->apiResponse(['success' => false, 'message' => $message], 200);
    }


    protected function respondWithResource(JsonResource $resource, $message = null, $statusCode = 200, $headers = []): JsonResponse
    {
        return $this->apiResponse(
            [
                'success' => true,
                'result' => $resource,
                'message' => $message
            ], $statusCode, $headers
        );
    }


    protected function respondWithResourceCollection(ResourceCollection $resourceCollection, $message = null, $statusCode = 200, $headers = []): JsonResponse
    {
        return $this->apiResponse(
            [
                'success' => true,
                'result' => $resourceCollection->response()->getData()
            ], $statusCode, $headers
        );
    }

    protected function respondUnAuthorized($message = 'Unauthorized'): JsonResponse
    {
        return $this->respondError($message, 401);
    }


    protected function respondError($message, int $statusCode = 400, Exception $exception = null, int $error_code = 1): JsonResponse
    {

        return $this->apiResponse(
            [
                'success' => false,
                'message' => $message ?? 'There was an internal error, Pls try again later',
                'exception' => $exception,
                'error_code' => $error_code
            ], $statusCode
        );
    }


    protected function respondForbidden($message = 'Forbidden'): JsonResponse
    {
        return $this->respondError($message, 403);
    }


    protected function respondNotFound($message = 'Not Found'): JsonResponse
    {
        return $this->respondError($message, 404);
    }


    protected function respondInternalError($message = 'Internal Error'): JsonResponse
    {
        return $this->respondError($message, 500);
    }

    protected function respondValidationErrors(ValidationException $exception): JsonResponse
    {
        return $this->apiResponse(
            [
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors()
            ],
            422
        );
    }
}
