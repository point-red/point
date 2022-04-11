<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferItemApprovalRequestSent extends Mailable
{
    use Queueable, SerializesModels;

    public $transferItems;
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
    public function __construct($transferItems, $approver, $form, $urlReferer, $ids, $token)
    {
        $this->transferItems = $transferItems;
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

        return $this->subject('Approval Email')
            ->view('emails.transfer-item.transfer-item-approval-sent', [
                'transferItems' => $this->transferItems,
                'approverId' => $this->approver->id,
                'fullName' => $this->approver->getFullNameAttribute(),
                'form' => $this->form,
                'url' => @$url,
                'ids' => $this->ids,
                'token' => $this->token
            ]);
    }
}
