<?php

namespace App\Exceptions;

use Exception;

class PointException extends Exception
{
    public function __construct($message = 'something went wrong')
    {
        parent::__construct($message, 422);
    }
}
