<?php

namespace App\Helpers\Journal;

use App\Model\Accounting\Journal;

class JournalHelper
{
    public static function insert()
    {
    }

    public static function delete($formId)
    {
        Journal::where('form_id', '=', $formId)->delete();
    }
}
