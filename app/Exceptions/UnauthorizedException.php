<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct()
    {
        $message = 'Unauthorized';

        parent::__construct($message, 422);
    }
}
