<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ApiExceptionHandler
{
    public function apiExceptions($request, Exception $exception)
    {
        /* Resource not found */
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Http not found',
                ],
            ], 404);
        }

        /* Model not found */
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Model not found',
                ],
            ], 404);
        }

        /*
         * Handle a failed validation attempt.
         *
         * @param  \Illuminate\Contracts\Validation\Validator  $validator
         * @return void
         *
         * @throws \Illuminate\Validation\ValidationException
         */
        if ($exception instanceof ValidationException) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ],
            ], 422);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => $exception->getMessage(),
                ],
            ], 403);
        }

        // Wrong access token
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $exception->getMessage(),
                ],
            ], 401);
        }

        // oauth server exception
        if ($exception instanceof OAuthServerException) {
            return response()->json([
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ],
            ], $exception->getCode());
        }

        /* Handle server error or library error */
        if ($exception->getCode() >= 500 || ! $exception->getCode()) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => 'Request Error',
                ],
            ], 500);
        }

        /* Handle other exception */
        return response()->json([
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ],
        ], $exception->getCode());
    }
}
