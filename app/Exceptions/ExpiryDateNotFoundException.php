<?php

namespace App\Exceptions;

use Exception;

class ExpiryDateNotFoundException extends Exception
{
    public function __construct($item)
    {
        parent::__construct('Expiry Date for Item '.$item->code.'-'.$item->name.' not found', 422);
    }
}
