<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ApiExceptionHandler {

    public function apiExceptions($request, Exception $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Http not found',
                ]
            ], 404);
        }

        /**
         * Handle a failed validation attempt.
         *
         * @param  \Illuminate\Contracts\Validation\Validator  $validator
         * @return void
         *
         * @throws \Illuminate\Validation\ValidationException
         */
        if ($exception instanceof ValidationException) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ]
            ], 422);
        }

        return response()->json([
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]
        ], $exception->getCode());
    }
}
