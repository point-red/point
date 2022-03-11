<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CashAdvanceBulkRequestApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cashAdvances;
    public $tenantBaseUrl;
    public $bulkId;

    /**
     * Create a new message instance.
     *
     * @param $employeeContractExpired
     * @param $employeeContractExpiredSoon
     */
    public function __construct($cashAdvances, $tenantBaseUrl, $bulkId)
    {
        $this->cashAdvances = $cashAdvances;
        $this->tenantBaseUrl = $tenantBaseUrl;
        $this->bulkId = $bulkId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.finance.cash-advance.bulk-request-approval-notification');
    }
}
