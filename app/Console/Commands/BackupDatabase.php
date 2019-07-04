<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Model\CloudStorage;
use Illuminate\Support\Str;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {project_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database client and tenant';

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
        $this->backupTenant();
    }

    private function backupTenant()
    {
        $project = Project::where('code', $this->argument('project_code'))->first();

        $this->line('backup database "'.$project->code.'"');
        $dbName = env('DB_DATABASE', 'point').'_'.strtolower($project->code);
        $projectCode = $project->code;
        $fileName = $dbName.'_'.date('Y-m-d_His');
        $fileExt = 'sql.gz';
        $file = $fileName.'.'.$fileExt;
        $temporaryFolder = 'tmp/'.$projectCode;
        $temporaryPath = $temporaryFolder.'/'.$file;
        $backupFolder = 'backup/database/'.$projectCode;
        $backupPath = $backupFolder.'/'.$file;

        $this->mySqlDump($dbName, $temporaryFolder, $file);

        $isFileExists = Storage::disk('local')->exists($temporaryPath);

        if (! $isFileExists) {
            $this->line('file not exists');

            return;
        }

        Storage::disk(env('STORAGE_DISK'))->put($backupPath, Storage::disk('local')->get($temporaryPath));

        Storage::disk('local')->delete($temporaryPath);

        $key = Str::random(16);

        $cloudStorage = new CloudStorage;
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'backup database';
        $cloudStorage->key = $key;
        $cloudStorage->path = $backupPath;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = $project->id;
        $cloudStorage->owner_id = null;
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();
    }

    private function mySqlDump($dbName, $temporaryFolder, $file)
    {
        $path = storage_path('app/'.$temporaryFolder);

        if (! file_exists($path)) {
            mkdir($path, 0700, true);
        }

        $mySqlDump = 'mysqldump -u '.env('DB_USERNAME').' -p'.env('DB_PASSWORD');

        $process = new Process($mySqlDump.' '.$dbName.' --quick | gzip > "'.$path.'/'.$file.'"');

        $process->setPTY(true);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $path;
    }
}
