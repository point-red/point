<?php

namespace App\Exceptions;

use Exception;

class BranchNotRightException extends Exception
{
    public function __construct()
    {
        parent::__construct("Doesn't belong to the right branch", 422);
    }
}
