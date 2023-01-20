<?php

namespace App\Exceptions;

use Exception;

class BranchNullException extends Exception
{
    public function __construct($action = 'save')
    {
        parent::__construct('please set as default branch', 422);
    }
}
