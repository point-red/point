<?php

namespace App\Exceptions;

use Exception;

class PointException extends Exception
{
    public function __construct()
    {
        parent::__construct('something went wrong', 422);
    }
}
