<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesVisitationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $project_name;
    protected $day_time;
    protected $sales_name;
    protected $call;
    protected $effective_call;
    protected $items;
    protected $value;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($project_name, $day_time, $sales_name, $call, $effective_call, $items, $value)
    {
        $this->project_name = $project_name;
        $this->day_time = $day_time;
        $this->sales_name = $sales_name;
        $this->call = $call;
        $this->effective_call = $effective_call;
        $this->items = $items;
        $this->value = $value;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('[' . title_case($this->project_name) . '] Sales Visitation ' . title_case($this->sales_name) . ' / ' . $this->day_time)
            ->view(['html' => 'emails.plugin.pinpoint.sales-visitation-notification'])
            ->with(['project_name' => $this->project_name,
                    'day_time' => $this->day_time,
                    'sales_name' => $this->sales_name,
                    'call' => $this->call,
                    'effective_call' => $this->effective_call,
                    'items' => $this->items,
                    'value' => $this->value]);
    }
}
