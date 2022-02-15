<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait ApiExceptionHandler
{
    public function apiExceptions($request, Throwable $exception)
    {
        /* Resource not found */
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'code' => 404,
                'message' => 'Http not found.',
            ], 404);
        }

        /* Model not found */
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'code' => 404,
                'message' => 'Model not found.',
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
                'code' => 422,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'code' => 403,
                'message' => $exception->getMessage(),
            ], 403);
        }

        // Wrong access token
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'code' => 401,
                'message' => $exception->getMessage(),
            ], 401);
        }

        // oauth server exception
        if ($exception instanceof OAuthServerException) {
            return response()->json([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ], $exception->getCode());
        }

        // handle form rules exception
        if ($exception instanceof IsReferencedException) {
            return response()->json([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'referenced_by' => $exception->getReferenced(),
            ], $exception->getCode());
        }

        if ($exception instanceof StockNotEnoughException || $exception instanceof ItemQuantityInvalidException) {
            return response()->json([
                'code' => 422,
                'message' => $this->getMessage(),
            ], 422);
        }

        if ($exception instanceof QueryException) {
            return response()->json([
                'code' => 400,
                'message' => $this->queryExceptionMessage($exception),
            ], 400);
        }

        /* Handle server error or library error */
        if ($exception->getCode() >= 500 || ! $exception->getCode()) {
            return response()->json([
                'code' => 500,
                'message' => 'Internal Server Error',
            ], 500);
        }

        /* Handle other exception */
        return response()->json([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ], $exception->getCode());
    }

    private function queryExceptionMessage($exception) {
        if (strpos($exception->getMessage(), 'Integrity constraint violation') !== false) {
            return "Duplicate data entry";
        }
        return "Invalid data";
    }
}
