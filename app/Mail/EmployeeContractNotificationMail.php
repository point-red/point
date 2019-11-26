<?php

namespace App\Mail;

use App\Model\HumanResource\Employee\EmployeeContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeContractNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employeeContractExpiredSoon;

    public $employeeContractExpired;

    /**
     * Create a new message instance.
     *
     * @param $employeeContractExpired
     * @param $employeeContractExpiredSoon
     */
    public function __construct($employeeContractExpiredSoon, $employeeContractExpired)
    {
        $this->employeeContractExpired = $employeeContractExpired;
        $this->employeeContractExpiredSoon = $employeeContractExpiredSoon;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.human-resource.employee-end-contract-notification');
    }
}
