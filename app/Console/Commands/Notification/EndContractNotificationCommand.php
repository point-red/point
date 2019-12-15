<?php

namespace App\Console\Commands\Notification;

use App\Helpers\Firebase\Firestore;
use App\Mail\EmployeeContractNotificationMail;
use App\Model\FirebaseToken;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\Master\User as TenantUser;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EndContractNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:end-contract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'End contract notification';

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
        $projects = Project::all();
        foreach ($projects as $project) {
            $this->line('end contract notification - '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $nextMonth = Carbon::now()->addMonth(1);

            $employeeContractExpiredSoon = EmployeeContract::where('contract_end', '>', now())->where('contract_end', '<', $nextMonth)->orderBy('contract_end', 'asc')->get();
            $employeeContractExpired= EmployeeContract::where('contract_end', '<', now())->orderBy('contract_end', 'asc')->get();

            $users = TenantUser::all();

            if ($employeeContractExpiredSoon->count() + $employeeContractExpired->count() > 0) {
                foreach ($users as $user) {
                    foreach ($user->getPermissions() as $permission) {
                        if ($permission == 'manage employee contract') {
                            Mail::to($user->email)->send(new EmployeeContractNotificationMail($employeeContractExpiredSoon, $employeeContractExpired));
                        }
                    }
                }
            }

//             $userIds = TenantUser::pluck('id')->toArray();
//
//             $userTokens = FirebaseToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
//
//            if ($employeeContracts->count() > 0) {
//                foreach ($employeeContracts as $employeeContract) {
//                    $message = 'Contract for '.$employeeContract->employee->name.' will end soon';
//
//                    $clickAction = $project->code.'.'.env('TENANT_DOMAIN').'/human-resource/employee/'.$employeeContract->employee_id;
//
//                    Artisan::call('push-notification', [
//                        'token' => $userTokens,
//                        'title' => 'Contract Expiration',
//                        'body' => $message,
//                        'click_action' => $clickAction,
//                    ]);
//
//                    Firestore::set('notifications', null, [
//                        'userId' => $project->owner->id,
//                        'projectId' => $project->id,
//                        'message' => $message,
//                        'clickAction' => $clickAction,
//                        'createdAt' => Carbon::parse(date('Y-m-d H:i:s'), 'UTC')->timezone($project->timezone)->toDateTimeString(),
//                    ]);
//                }
//            }
        }
    }
}
