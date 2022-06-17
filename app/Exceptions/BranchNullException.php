<?php

namespace App\Exceptions;

use Exception;

class BranchNullException extends Exception
{
    public function __construct($action = 'save')
    {
        parent::__construct('please set default branch to '. $action .' this form', 422);
    }
}
