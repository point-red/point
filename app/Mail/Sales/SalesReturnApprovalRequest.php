<?php

namespace App\Mail\Sales;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesReturnApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $salesReturns;
    public $approver;
    public $approverToken;
    public $form;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($salesReturns, $approver, $form)
    {
        $this->salesReturns = $salesReturns;
        $this->approver = $approver;
        $this->approverToken = $approver->token;
        $this->form = $form;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->approver->token = $this->approverToken;

        $user = $this->form->send_by;

        return $this->subject('Request Approval All')
            ->from($user->email, $user->getFullNameAttribute())
            ->view('emails.sales.return.return-approval-request', [
                'salesReturns' => $this->salesReturns,
                'approver' => $this->approver,
                'form' => $this->form
            ]);
    }
}
