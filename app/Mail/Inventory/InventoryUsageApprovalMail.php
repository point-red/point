<?php

namespace App\Mail\Inventory;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InventoryUsageApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inventoryUsage;
    public $approver;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inventoryUsage, $approver)
    {
        $this->inventoryUsage = $inventoryUsage;
        $this->approver = $approver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Approval Email')
            ->view('emails.inventory.inventory-usage.inventory-usage-approval', [
                'inventoryUsage' => $this->inventoryUsage,
                'approver' => $this->approver,
                'form' => $this->inventoryUsage->form,
            ]);
    }
}
