<?php

namespace App\Console\Commands;

use App\Model\HumanResource\Employee\Employee;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlterConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-console';

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
        $projects = Project::where('is_generated', true)->get();
        foreach ($projects as $project) {
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();
            $employees = Employee::where('updated_at', '>', '2020-04-21')->get();
            if ($employees->count() > 0) {
                $this->line('PROJECT:  '.$project->code.' = '.$employees->count());

                foreach ($employees as $employee) {
                    $this->line($employee->id.'. '.$employee->name.' ('.$employee->updated_at.')');
                }
            }
        }
    }
}
