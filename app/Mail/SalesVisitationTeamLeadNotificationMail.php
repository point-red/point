<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesVisitationTeamLeadNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $projectCode;
    protected $projectName;
    protected $date;
    protected $totalVisitation;

    /**
     * Create a new message instance.
     *
     * @param $projectCode
     */
    public function __construct($projectCode, $projectName, $date, $totalVisitation)
    {
        $this->projectCode = $projectCode;
        $this->projectName = $projectName;
        $this->date = $date;
        $this->totalVisitation = $totalVisitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('['.title_case($this->projectName).'] Sales Visitation')
            ->view(['html' => 'emails.plugin.pinpoint.sales-visitation-team-lead-notification'])
            ->with([
                'projectCode' => $this->projectCode,
                'projectName' => $this->projectName,
                'date' => $this->date,
                'totalVisitation' => $this->totalVisitation,
            ]);
    }
}
