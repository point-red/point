<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Requests\Project\Project\StoreProjectRequest;
use App\Http\Requests\Project\Project\UpdateProjectRequest;
use App\Http\Resources\Project\Project\ProjectResource;
use App\Model\Master\User;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use App\Http\Resources\Project\Project\ProjectCollection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Project\Project\ProjectCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new ProjectCollection(Project::where('owner_id', auth()->user()->id)->paginate($limit));
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
        if ($numberOfProject > 1) {
            return response()->json([
                'code' => 422,
                'message' => 'Beta user only allowed to create 1 project'
            ], 422);
        }

        // Create new database for tenant project
        $dbName = 'point_' . strtolower($request->get('code'));
        Artisan::call('tenant:create-database', [
            'db_name' => $dbName
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
        $project->save();

        // Migrate database
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true
        ]);

        // Clone user point into their database
        $user = new User;
        $user->id = auth()->user()->id;
        $user->name = auth()->user()->name;
        $user->email = auth()->user()->email;
        $user->password = auth()->user()->password;
        $user->phone_confirmation_code = auth()->user()->phone_confirmation_code;
        $user->phone_confirmed = auth()->user()->phone_confirmed;
        $user->email_confirmation_code = auth()->user()->email_confirmation_code;
        $user->email_confirmed = auth()->user()->email_confirmed;
        $user->save();

        Artisan::call('tenant:setup-database');

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
        $project->save();

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        $project->delete();

        // Delete database tenant
        Artisan::call('tenant:delete-database', [
            'db_name' => 'point_' . strtolower($project->code)
        ]);

        return new ProjectResource($project);
    }
}
