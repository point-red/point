<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\SalesVisitationTeamLeadNotificationMail;
use App\Model\Master\User;
use App\Model\Project\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Model\Plugin\PinPoint\SalesVisitation;

class SalesVisitationNotificationTeamLeadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:sales-visitation-team-lead';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Scheduled Sales Visitation Team Lead Notification';

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
     * @return void
     */
    public function handle()
    {
        $this->line('sending sales visitation supervisor notification');

        $yesterdayDate = date('Y-m-d 00:00:00', strtotime('-1 days'));

        $projects = Project::all();

        foreach ($projects as $project) {
            $this->line($project->code);
            $databaseName = 'point_'.strtolower($project->code);
            $this->line('Notification : '.$project->code);

            // Update tenant database name in configuration
            config()->set('database.connections.tenant.database', strtolower($databaseName));
            DB::connection('tenant')->reconnect();
            config()->set('mail.from.name', capitalize($project->name));

            $salesVisitationForm = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
                ->with('form')
                ->select('pin_point_sales_visitations.*');

            $dateFrom = date('Y-m-d 00:00:00', strtotime($yesterdayDate));
            $dateTo = date('Y-m-d 23:59:59', strtotime($yesterdayDate));
            $salesVisitationForm = $salesVisitationForm->whereBetween('forms.date', [$dateFrom, $dateTo])->get();

            $this->line($salesVisitationForm->count());
            if ($salesVisitationForm->count() == 0) {
                continue;
            }

            $allUser = User::select(['id', 'email'])->get();
            $userEmails = [];
            foreach ($allUser as $user) {
                if (($user->hasPermissionTo('notification pin point supervisor'))) {
                    $this->line($user->email);
                    array_push($userEmails, $user->email);
                }
            }

            if (count($userEmails) > 0) {
                $this->line(count($userEmails));
                Mail::to($userEmails)->queue(new SalesVisitationTeamLeadNotificationMail($project->code, $project->name, $yesterdayDate, $salesVisitationForm->count()));
            }
        }
    }
}
