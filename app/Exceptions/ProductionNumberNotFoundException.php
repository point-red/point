<?php

namespace App\Exceptions;

use Exception;

class ProductionNumberNotFoundException extends Exception
{
    public function __construct($item)
    {
        parent::__construct('Production Number for Item '.$item->code.'-'.$item->name.' not found', 422);
    }
}
