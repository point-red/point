<?php

namespace App\Exceptions;

use Exception;

class ItemQuantityInvalidException extends Exception
{
    public function __construct($item)
    {
        parent::__construct('Item '.$item->code.'-'.$item->name.' quantity invalid', 422);
    }
}
