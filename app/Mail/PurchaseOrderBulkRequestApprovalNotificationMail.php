<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderBulkRequestApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseOrders;
    public $tenant;
    public $tenantUrl;
    public $bulkId;
    public $token;
    public $projectName;

    /**
     * Create a new message instance.
     *
     * @param $employeeContractExpired
     * @param $employeeContractExpiredSoon
     */
    public function __construct($purchaseOrders, $tenant, $tenantUrl, $bulkId, $token, $projectName)
    {
        $this->purchaseOrders = $purchaseOrders;
        $this->tenant = $tenant;
        $this->tenantUrl = $tenantUrl;
        $this->bulkId = $bulkId;
        $this->token = $token;
        $this->projectName = $projectName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Approval Email Purchase Order')
                ->view('emails.purchase.purchase-order.bulk-request-approval-notification');
    }
}
