<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DatabaseBackupController extends Controller
{
    public function index(Request $request)
    {
        $project = Project::where('code', $request->get('code'))->first();

        $backups = CloudStorage::where('feature', 'backup database')
            ->where('project_id', $project->id)
            ->get();

        return new ApiCollection($backups);
    }

    public function store(Request $request)
    {
        $project = Project::where('code', $request->get('code'))->first();

        $backups = CloudStorage::where('feature', 'backup database')
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime(now())),
                date('Y-m-d 23:59:59', strtotime(now()))
            ])
            ->get();

        if ($backups->count() > 5) {
            return response()->json([
                'message' => 'You reach maximum limit of backup today'
            ], 422);
        }

        Artisan::call('db:backup', ['project_code' => $request->get('code')]);

        return response()->json([], 201);
    }
}
