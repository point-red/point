<?php

namespace App\Mail\Plugin\PlayBook\Approval;

use App\Model\Plugin\PlayBook\Instruction;
use App\Model\Plugin\PlayBook\Procedure;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestSent extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $approver;
    public $urlReferer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $approver, $urlReferer)
    {
        if ($type === Procedure::class) {
            $this->type = 'procedure';
        } elseif ($type === Instruction::class) {
            $this->type = 'instruction';
        }

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
            $parsedUrl = @parse_url($this->urlReferer);
            $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}/plugin/play-book/approval/{$this->type}";
        }

        return $this->subject("New {$this->type}")
            ->view('emails.plugin.play-book.approval-sent', [
                'type' => $this->type,
                'name' => $this->approver->name,
                'url' => @$url,
            ]);
    }
}
