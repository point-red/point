<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiveItemApprovalRequestSent extends Mailable
{
    use Queueable, SerializesModels;

    public $receiveItem;
    public $updated_by;
    public $urlReferer;
    public $token;
    public $form_send_done;
    public $crud_type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($receiveItem, $updated_by, $urlReferer, $token, $form_send_done, $crud_type)
    {
        $this->receiveItem = $receiveItem;
        $this->updated_by = $updated_by;
        $this->urlReferer = $urlReferer;
        $this->token = $token;
        $this->form_send_done = $form_send_done;
        $this->crud_type = $crud_type;
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
            ->view('emails.transfer-item.receive-item-approval-sent', [
                'receiveItem' => $this->receiveItem,
                'approverId' => $this->receiveItem->form->requestApprovalTo->id,
                'updated_by' => $this->updated_by,
                'fullName' => $this->receiveItem->form->requestApprovalTo->getFullNameAttribute(),
                'url' => @$url,
                'token' => $this->token,
                'formSendDone'=> $this->form_send_done,
                'crudType'=> $this->crud_type
            ]);
    }
}
