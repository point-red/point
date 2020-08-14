<?php

namespace App\Exceptions;

use Exception;

class BranchNullException extends Exception
{
    public function __construct()
    {
        parent::__construct('please set default branch to save this form', 422);
    }
}
