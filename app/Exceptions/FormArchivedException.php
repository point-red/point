<?php

namespace App\Exceptions;

use Exception;

class FormArchivedException extends Exception
{
    public function __construct()
    {
        $message = 'Form archived';

        parent::__construct($message, 422);
    }
}
