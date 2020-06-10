<?php

namespace App\Mail\Plugin\PlayBook\Approval;

use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\Procedure;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProcedureApprovalRequestSent extends Mailable
{
    use Queueable, SerializesModels;

    public $procedure;
    public $approver;
    public $urlReferer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Procedure $procedure, $approver, $urlReferer)
    {
        $this->procedure = $procedure;
        $this->approver = $approver;
        $this->urlReferer = $urlReferer;
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
            $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$port}/plugin/play-book/procedure/{$this->procedure->id}";
        }

        if ($this->procedure->approval_action === 'destroy') {
            $subject = "Procedure Deletion Request";
        } else {
            $subject = "New Procedure";
        }

        return $this->subject($subject)
            ->view('emails.plugin.play-book.procedure-approval-sent', [
                'procedure' => $this->procedure,
                'name' => $this->approver->name,
                'url' => @$url,
            ]);
    }
}
