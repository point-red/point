<?php

namespace App\Exceptions;

use Exception;

class InputBackDateForbiddenException extends Exception
{
    public function __construct($audit, $item)
    {
        parent::__construct('Input error because' .
            $item->label .' already audited in '.$audit->form->number.' on ' .
            date('d F Y H:i', strtotime($audit->form->date)), 422);
    }
}
