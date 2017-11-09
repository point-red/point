<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
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

        /* Handle server error or library error */
        if ($exception->getCode() >= 500 || ! $exception->getCode()) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'message' => 'Something wrong with server',
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
