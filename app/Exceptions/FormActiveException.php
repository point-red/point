<?php

namespace App\Exceptions;

use Exception;

class FormActiveException extends Exception
{
    public function __construct()
    {
        parent::__construct('please delete this form before edit', 422);
    }
}
