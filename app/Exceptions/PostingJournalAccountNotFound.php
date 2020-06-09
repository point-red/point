<?php

namespace App\Exceptions;

use Exception;

class PostingJournalAccountNotFound extends Exception
{
    public function __construct($feature, $name)
    {
        parent::__construct("Journal $feature account - $name not found", 422);
    }
}
