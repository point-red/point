<?php

namespace App\Console\Commands;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeEmail;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MaskingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:masking-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary';

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
        if (env('APP_ENV') == 'local') {
            $projects = Project::all();
            foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $users = \App\Model\Master\User::all();
            foreach ($users as $key => $user) {
                $user->email = $key . 'mail@mail.mail';
                $user->name = 'user' . $key;
                $user->address = 'address' . $key;
                $user->phone = 'phone' . $key;
                $user->first_name = 'user' . $key;
                $user->last_name = 'user' . $key;
                $user->save();
            }

            $employees = Employee::all();
            foreach ($employees as $key => $employee) {
                $employee->personal_identity = rand(10000000, 99990000) . '-' . $key;
                $employee->name = 'user' . $key;
                $employee->last_education = 'last_education' . $key;
                $employee->married_with = 'married_with' . $key;
                $employee->save();
            }

            $addresses = Employee\EmployeeAddress::all();
            foreach ($addresses as $key => $address) {
                $address->address = 'address-' . $key;
                $address->save();
            }

            $phones = Employee\EmployeePhone::all();
            foreach ($phones as $key => $phone) {
                $phone->phone = 'phone-' . $key;
                $phone->save();
            }

            $emails = Employee\EmployeeCompanyEmail::all();
            foreach ($emails as $key => $email) {
                $email->email = 'email-' . $key . '@a.cm';
                $email->save();
            }

            $emails = EmployeeEmail::all();
            foreach ($emails as $key => $email) {
                $email->email = 'email-' . $key . '@a.cm';
                $email->save();
            }

            $groups = EmployeeGroup::all();
            foreach ($groups as $key => $group) {
                $group->name = 'group-' . $key;
                $group->save();
            }

            $medias = EmployeeSocialMedia::all();
            foreach ($medias as $key => $media) {
                $media->account = 'account-' . $key;
                $media->save();
            }
        }
        }
    }
}
