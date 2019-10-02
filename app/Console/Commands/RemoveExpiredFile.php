<?php

namespace App\Console\Commands;

use App\Model\CloudStorage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveExpiredFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud-storage:remove-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired file from cloud storage';

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
        $this->line('Get expired file list');
        $cloudStorages = CloudStorage::where('expired_at', '<', Carbon::now())->get();

        if ($cloudStorages->count() == 0) {
            $this->line('Expired file not found');
        }

        foreach ($cloudStorages as $cloudStorage) {
            $this->line('Remove ' . $cloudStorage->file_name);

            $result = Storage::disk($cloudStorage->disk)->delete($cloudStorage->path);

            if ($result) {
                $cloudStorage->delete();
            } else {
                $this->line('Failed to remove ' . $cloudStorage->file_name);
            }
        }

        $this->line('Done');
    }
}
