<?php

namespace App\Exceptions;

use App\Model\Inventory\InventoryAudit\InventoryAudit;
use Exception;

class InputBackDateForbiddenException extends Exception
{
    public function __construct($audit, $item)
    {
        $form = $audit->form ?? InventoryAudit::find($audit->id)->form;
        parent::__construct('Input error because'.
            $item->label.' already audited in '.$form->number.' on '.
            date('d F Y H:i', strtotime($form->date)), 422);
    }
}
