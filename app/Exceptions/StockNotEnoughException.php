<?php

namespace App\Exceptions;

use Exception;

class StockNotEnoughException extends Exception
{
    public function __construct($item)
    {
        parent::__construct('Stock '.$item->label.' not enough', 422);
    }
}
