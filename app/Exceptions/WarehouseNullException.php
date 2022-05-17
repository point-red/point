<?php

namespace App\Exceptions;

use Exception;

class WarehouseNullException extends Exception
{
    public function __construct($action = 'save')
    {
        parent::__construct('please set default warehouse to '. $action .' this form', 422);
    }
}
