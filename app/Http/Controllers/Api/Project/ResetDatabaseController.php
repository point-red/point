<?php

namespace App\Http\Controllers\Api\Project;

use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseController extends Controller
{
    public function index($id)
    {
        $project = Project::find($id);

        if (auth()->user()->id == $project->owner_id) {
            Artisan::call('tenant:database:reset', [
                'project_code' => $project->code,
            ]);
        }
    }
}
