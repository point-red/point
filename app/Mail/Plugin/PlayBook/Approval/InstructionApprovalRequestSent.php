<?php

namespace App\Mail\Plugin\PlayBook\Approval;

use App\Model\Plugin\PlayBook\Instruction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstructionApprovalRequestSent extends Mailable
{
    use Queueable, SerializesModels;

    public $instruction;
    public $approver;
    public $urlReferer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Instruction $instruction, $approver, $urlReferer)
    {
        $this->instruction = $instruction;
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
            $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$port}/plugin/play-book/approval/instruction";
        }

        return $this->subject("New Instruction")
            ->view('emails.plugin.play-book.instruction-approval-sent', [
                'instruction' => $this->instruction,
                'name' => $this->approver->name,
                'url' => @$url,
            ]);
    }
}
