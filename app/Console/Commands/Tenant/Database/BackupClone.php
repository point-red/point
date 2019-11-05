<?php

namespace App\Console\Commands\Tenant\Database;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupClone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:database:backup-clone {project_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup tenant database using clone strategy';

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
        $backupDatabase = 'backup_'.$this->argument('project_code');
        $sourceDatabase = env('DB_DATABASE').'_'.$this->argument('project_code');

        // drop tenant database if exists
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "drop database if exists '.$backupDatabase.'"');
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }

        // create new tenant database
        $process = new Process('mysql -u '.env('DB_TENANT_USERNAME').' -p'.env('DB_TENANT_PASSWORD').' -e "create database '.$backupDatabase.'"');
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }

        // clone source database to backup database
        $process = new Process('mysqldump -u '.env('DB_TENANT_USERNAME')
            .' -p'.env('DB_TENANT_PASSWORD').' '.$sourceDatabase
            .' | mysql -u '.env('DB_TENANT_USERNAME')
            .' -p'.env('DB_TENANT_PASSWORD').' '.$backupDatabase);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            $this->line($process->getOutput());
            throw new ProcessFailedException($process);
        }
    }
}
