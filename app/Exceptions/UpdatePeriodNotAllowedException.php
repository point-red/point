<?php

namespace App\Exceptions;

use Exception;

class UpdatePeriodNotAllowedException extends Exception
{
    public function __construct()
    {
        $message = 'Update period not allowed';

        parent::__construct($message, 422);
    }
}
