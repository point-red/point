<?php

namespace App\Console\Commands\Hub;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateHubDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hub:database:create {db_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database for tenant';

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
        // tenant subdomain equal to tenant database name
        $dbName = $this->argument('db_name');

        // drop tenant database if exists
        $process = Process::fromShellCommandline('mysql -h '.env('DB_HOST').' -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD').' -e "drop database if exists '.$dbName.'"');
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }

        // create new tenant database
        $process = Process::fromShellCommandline('mysql -h '.env('DB_HOST').' -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD').' -e "create database '.$dbName.'"');
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }
    }
}
