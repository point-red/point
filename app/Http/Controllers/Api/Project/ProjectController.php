<?php

namespace App\Http\Controllers\Api\Project;

use App\Model\Master\User;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\Project\Project\ProjectResource;
use App\Http\Requests\Project\Project\StoreProjectRequest;
use App\Http\Requests\Project\Project\DeleteProjectRequest;
use App\Http\Requests\Project\Project\UpdateProjectRequest;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Controllers\Api\Project\ApiCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        $projects = Project::join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->where('project_user.user_id', auth()->user()->id)
            ->select('projects.*', 'user_id', 'user_name', 'user_email', 'joined', 'request_join_at', 'project_user.id as user_invitation_id')
            ->paginate($limit);

        return new ApiCollection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function store(StoreProjectRequest $request)
    {
        // User only allowed to create max 1 project
        $numberOfProject = Project::where('owner_id', auth()->user()->id)->count();
        if ($numberOfProject >= 1) {
            return response()->json([
                'code' => 422,
                'message' => 'Beta user only allowed to create 1 project',
            ], 422);
        }

        // Create new database for tenant project
        $dbName = 'point_'.strtolower($request->get('code'));
        Artisan::call('tenant:database:create', [
            'db_name' => $dbName,
        ]);

        // Update tenant database name in configuration
        config()->set('database.connections.tenant.database', $dbName);
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        $project = new Project;
        $project->owner_id = auth()->user()->id;
        $project->code = $request->get('code');
        $project->name = $request->get('name');
        $project->address = $request->get('address');
        $project->phone = $request->get('phone');
        $project->vat_id_number = $request->get('vat_id_number');
        $project->invitation_code = get_invitation_code();
        $project->save();

        $projectUser = new ProjectUser;
        $projectUser->project_id = $project->id;
        $projectUser->user_id = $project->owner_id;
        $projectUser->user_name = $project->owner->name;
        $projectUser->user_email = $project->owner->email;
        $projectUser->joined = true;
        $projectUser->save();

        // Migrate database
        Artisan::call('tenant:migrate', $dbName);

        // Clone user point into their database
        $user = new User;
        $user->id = auth()->user()->id;
        $user->name = auth()->user()->name;
        $user->first_name = auth()->user()->first_name;
        $user->last_name = auth()->user()->last_name;
        $user->email = auth()->user()->email;
        $user->save();

        Artisan::call('tenant:seed-fresh-project');

        DB::connection('tenant')->commit();

        return new ProjectResource($project);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function show($id)
    {
        return new ProjectResource(Project::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Project\Project\UpdateProjectRequest $request
     * @param  int                                                    $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function update(UpdateProjectRequest $request, $id)
    {
        // Update tenant database name in configuration
        $project = Project::findOrFail($id);
        $project->name = $request->get('name');
        $project->address = $request->get('address');
        $project->phone = $request->get('phone');
        $project->vat_id_number = $request->get('vat_id_number');
        $project->invitation_code = $request->get('invitation_code');
        $project->invitation_code_enabled = $request->get('invitation_code_enabled');
        $project->save();

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Project\Project\DeleteProjectRequest $request
     * @param  int                                                    $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function destroy(DeleteProjectRequest $request, $id)
    {
        $project = Project::findOrFail($id);

        $project->delete();

        // Delete database tenant
        Artisan::call('tenant:database:delete', [
            'db_name' => 'point_'.strtolower($project->code),
        ]);

        return new ProjectResource($project);
    }
}
