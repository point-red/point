<?php

namespace App\Jobs;

use App\Mail\ExportNotificationMail;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class FinishingExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $userId;
    protected $cloudStorage;
    protected $tenant;
    protected $fileName;
    protected $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, CloudStorage $cloudStorage, $tenant, $fileName, $path)
    {
        $this->userId = $userId;
        $this->cloudStorage = $cloudStorage;
        $this->tenant = $tenant;
        $this->fileName = $fileName;
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        config()->set('database.connections.tenant.database', env('DB_DATABASE', 'point').'_'.$this->tenant);
        $this->cloudStorage->updated_at = Carbon::now();
        $this->cloudStorage->expired_at = Carbon::now()->addDay(1);
        $this->cloudStorage->percentage = 100;
        $this->cloudStorage->save();
        $user = tenant($this->userId);
        Mail::queue(new ExportNotificationMail($user, $this->fileName, $this->path, $this->tenant));
    }
}
