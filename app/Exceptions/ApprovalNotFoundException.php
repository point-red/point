<?php

namespace App\Exceptions;

use Exception;

class ApprovalNotFoundException extends Exception
{
    public function __construct()
    {
        $message = 'Approval not found';

        parent::__construct($message, 422);
    }
}
