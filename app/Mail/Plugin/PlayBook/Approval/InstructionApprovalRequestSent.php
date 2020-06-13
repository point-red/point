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
    public $steps;
    public $approver;
    public $urlReferer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Instruction $instruction, $steps, $approver, $urlReferer)
    {
        $this->instruction = $instruction;
        $this->approver = $approver;
        $this->urlReferer = $urlReferer;
        $this->steps = $steps;
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
            $baseUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$port}/plugin/play-book";

            if (!$this->steps || ($this->steps && $this->steps->count() < 1)) {
                $url = "{$baseUrl}/approval/instruction?id={$this->instruction->id}&action=approve";
            } else {
                $url = "{$baseUrl}/instruction?"
                    . "procedure={$this->instruction->procedure_id}"
                    . "&instruction={$this->instruction->id}";

                foreach ($this->steps as $step) {
                    $url .= "&review[]={$step->id}";
                }
            }
        }

        return $this->subject("New Instruction")
            ->view('emails.plugin.play-book.instruction-approval-sent', [
                'instruction' => $this->instruction,
                'steps' => $this->steps,
                'name' => $this->approver->name,
                'url' => @$url,
            ]);
    }
}
