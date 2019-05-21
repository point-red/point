<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesVisitationNotificationSupervisorMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $project_name;
    protected $day_time;
    protected $user_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($project_name, $day_time, $user_data)
    {
        $this->project_name = $project_name;
        $this->day_time = $day_time;
        $this->user_data = $user_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('['.title_case($this->project_name).'] Sales Visitation '.' / '.$this->day_time)
            ->view(['html' => 'emails.plugin.pinpoint.sales-visitation-notification-supervisor'])
            ->with(['project_name' => $this->project_name,
                    'day_time' => $this->day_time,
                    'user_data' => $this->user_data, ]);
    }
}
