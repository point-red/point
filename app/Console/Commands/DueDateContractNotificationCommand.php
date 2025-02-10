<?php

namespace App\Console\Commands;

use App\Mail\DueDateReminderContractEmail;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeReviewer;
use App\Model\Master\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class DueDateContractNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:due-date-contract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Due date contract notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startOfDay = Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay();
        $endOfDay = Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay();

        $this->info('Current time: ' . $startOfDay);

        $contract_reminders = EmployeeContract::whereBetween('contract_due_date', [$startOfDay, $endOfDay])->get();

        if ($contract_reminders->isEmpty()) {
            $this->info('No contract due date today');
            return;
        }

        foreach ($contract_reminders as $contract) {
            $employee = Employee::find($contract->employee_id);
            $reviewers = EmployeeReviewer::where('employee_id', $contract->employee_id)->get();

            if ($reviewers->isEmpty()) {
                $this->info('No reviewer for employee ' . $contract->employee->name);
            }

            foreach ($reviewers as $reviewer) {
                $reviewer = User::find($reviewer->user_id);
                if ($reviewer->email) {
                    $this->info('Sending due date contract notification to ' . $reviewer->email);
                    Mail::to($reviewer->email)->send(new DueDateReminderContractEmail(
                        $employee,
                        $reviewer,
                        $contract
                    ));
                }
            }
        }

        $this->info('Due date contract notification sent');
        return;
    }
}
