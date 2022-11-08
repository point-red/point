<?php

namespace App\Mail\Finance\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCancellationApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $approver;
    public $form;
    public $urlReferer;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment, $approver, $form, $token)
    {
        $this->payment = $payment;
        $this->approver = $approver;
        $this->form = $form;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Cancellation Approval Email')
            ->view('emails.finance.payment.cancellation-approval', [
                'payment' => $this->payment,
                'approverId' => $this->approver->id,
                'fullName' => $this->approver->getFullNameAttribute(),
                'form' => $this->form,
                'token' => $this->token,
                'cashAdvancePayment' => $this->payment->cashAdvance
            ]);
    }
}
