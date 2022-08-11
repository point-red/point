<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCollectionCancellationApprovalRequestSentSingle extends Mailable
{
    use Queueable, SerializesModels;

    public $paymentCollection;
    public $approver;
    public $form;
    public $urlReferer;
    public $ids;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($paymentCollection, $approver, $form, $urlReferer, $ids, $token)
    {
        $this->paymentCollection = $paymentCollection;
        $this->approver = $approver;
        $this->form = $form;
        $this->urlReferer = $urlReferer;
        $this->ids = $ids;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (@$this->urlReferer) {
            $parsedUrl = parse_url($this->urlReferer);
            $port = @$parsedUrl['port'] ? ":{$parsedUrl['port']}" : '';
            $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$port}/";
        }

        return $this->subject('Cancellation Approval Email')
            ->view('emails.payment-collection.payment-collection-cancellation-approval', [
                'paymentCollections' => $this->paymentCollection,
                'approverId' => $this->approver->id,
                'fullName' => $this->approver->getFullNameAttribute(),
                'form' => $this->form,
                'url' => @$url,
                'ids' => $this->ids,
                'token' => $this->token
            ]);
    }
}
