<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class KpiReminderEmail extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(Request $request)
    {
        return $this->view('emails/human-resource/kpi-reminder')
            ->with(
            [
                'name' => $request->get('name'),
                'employeeName' => $request->get('employeeName'),
                'periode' => $request->get('startDate').' ~ '.$request->get('endDate')
            ]);
    }
}
