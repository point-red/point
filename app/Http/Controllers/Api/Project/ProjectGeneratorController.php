<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Project\DeleteProjectRequest;
use App\Http\Requests\Project\Project\StoreProjectRequest;
use App\Http\Requests\Project\Project\UpdateProjectRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Project\Project\ProjectResource;
use App\Model\Master\User;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProjectGeneratorController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequest $request
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function store(StoreProjectRequest $request)
    {
        // User only allowed to create max 1 project
        $numberOfProject = Project::where('owner_id', auth()->user()->id)->count();
            // TODO: disable new project creation
        if ($numberOfProject >= 100) {
            return response()->json([
                'code' => 422,
                'message' => 'We are updating our server, currently you cannot create new project',
            ], 422);
        }

        $project = new Project;
        $project->owner_id = auth()->user()->id;
        $project->code = $request->get('code');
        $project->name = $request->get('name');
        $project->group = $request->get('group');
        $project->timezone = $request->get('timezone');
        $project->address = $request->get('address');
        $project->phone = $request->get('phone');
        $project->whatsapp = $request->get('whatsapp');
        $project->website = $request->get('website');
        $project->marketplace_notes = $request->get('marketplace_notes');
        $project->vat_id_number = $request->get('vat_id_number');
        $project->invitation_code = get_invitation_code();
        $project->is_generated = false;
        $project->save();

        $projectUser = new ProjectUser;
        $projectUser->project_id = $project->id;
        $projectUser->user_id = $project->owner_id;
        $projectUser->user_name = $project->owner->name;
        $projectUser->user_email = $project->owner->email;
        $projectUser->joined = true;
        $projectUser->save();

        // Create new database for tenant project
        $dbName = env('DB_DATABASE').'_'.strtolower($request->get('code'));
        Artisan::call('tenant:database:create', ['db_name' => $dbName]);

        // Update tenant database name in configuration
        config()->set('database.connections.tenant.database', $dbName);
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        // Migrate database
        Artisan::call('tenant:migrate', ['db_name' => $dbName]);

        // Clone user point into their database
        $user = new User;
        $user->id = auth()->user()->id;
        $user->name = auth()->user()->name;
        $user->first_name = auth()->user()->first_name;
        $user->last_name = auth()->user()->last_name;
        $user->email = auth()->user()->email;
        $user->save();

        Artisan::call('tenant:seed:first', ['db_name' => $dbName]);

        DB::connection('tenant')->commit();

        return new ProjectResource($project);
    }
}
