<?php

namespace App\Exceptions;

use Exception;

class ProductionNumberNotExistException extends Exception
{
    public function __construct($item, $productionNumber, $warehouse)
    {
        parent::__construct('Item '.$item->code.'-'.$item->name.' with production number '.$productionNumber.' not exist in '.$warehouse->code.'-'.$warehouse->name, 422);
    }
}
