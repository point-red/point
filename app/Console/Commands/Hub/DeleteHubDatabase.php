<?php

namespace App\Console\Commands\Hub;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DeleteHubDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hub:database:delete {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop hub database';

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
        // hub database name
        $dbName = $this->argument('db_name');

        // drop hub database if exists
        $process = Process::fromShellCommandline('mysql -h '.env('DB_HOST').' -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD').' -e "drop database if exists '.$dbName.'"');
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }
    }
}
