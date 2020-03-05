<?php

namespace App\Console\Commands;

use App\Model\CloudStorage;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database';

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
        $cloudStorage = CloudStorage::where('key', $this->argument('key'))->first();

        $dbName = env('DB_DATABASE').'_'.$cloudStorage->project->code;

        $mySqlDump = 'curl "'.$cloudStorage->download_url.'" | gunzip | mysql -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD');

        $this->line($mySqlDump);

        $process = Process::fromShellCommandline($mySqlDump.' '.$dbName);

        $process->setPTY(true);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
