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
    public $urlReferer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($salesReturns, $approver, $form, $urlReferer)
    {
        $this->salesReturns = $salesReturns;
        $this->approver = $approver;
        $this->approverToken = $approver->token;
        $this->form = $form;
        $this->urlReferer = $urlReferer;
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
        if (count($this->salesReturns) > 1) {
            return $this->subject('Request Approval All')
                ->from($user->email, $user->getFullNameAttribute())
                ->view('emails.sales.return.return-approval-request', [
                    'salesReturns' => $this->salesReturns,
                    'approver' => $this->approver,
                    'form' => $this->form
                ]);
        } else {
            if (@$this->urlReferer) {
                $parsedUrl = parse_url($this->urlReferer);
                $port = @$parsedUrl['port'] ? ":{$parsedUrl['port']}" : '';
                $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$port}/";
            }

            return $this->subject('Approval Request')
                ->from($user->email, $user->getFullNameAttribute())
                ->view('emails.sales.return.return-approval-request-single', [
                    'salesReturns' => $this->salesReturns,
                    'approver' => $this->approver,
                    'form' => $this->form,
                    'url' => @$url,
                ]);
        }
    }
}
