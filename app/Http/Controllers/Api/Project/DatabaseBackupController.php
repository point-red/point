<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Illuminate\Support\Facades\Artisan;

class DatabaseBackupController extends Controller
{
    public function index($id)
    {
        $backups = CloudStorage::where('feature', 'backup database')
            ->where('project_id', $id)
            ->get();

        return new ApiCollection($backups);
    }

    public function store($id)
    {
        $project = Project::findOrFail($id);

        $backups = CloudStorage::where('feature', 'backup database')
            ->where('project_id', $id)
            ->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime(now())),
                date('Y-m-d 23:59:59', strtotime(now())),
            ])->get();

        if ($backups->count() > 5) {
            return response()->json([
                'message' => 'You reach maximum limit of backup today',
            ], 422);
        }

        Artisan::call('db:backup', ['project_code' => $project->code]);

        return response()->json([], 201);
    }
}
