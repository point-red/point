<?php

namespace App\Exceptions;

use Exception;

class IsReferencedException extends Exception
{
    public $referenced_by;

    public function __construct($message = null, $references = [])
    {
        parent::__construct($message, 422);

        $this->referenced_by = $references;
    }

    public function getReferenced()
    {
        return $this->referenced_by;
    }
}
