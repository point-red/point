<?php

namespace App\Http\Controllers\Api\Project;

use App\User;
use App\Model\Auth\Role;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Master\UserInvitation\UserInvitationResource;
use App\Http\Resources\Master\UserInvitation\UserInvitationCollection;

class RequestJoinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Master\UserInvitation\UserInvitationCollection
     */
    public function index(Request $request)
    {
        $projectUsers = ProjectUser::whereNotNull('request_join_at')
            ->where('joined', false)
            ->where('project_id', $request->get('project_id'))
            ->get();

        return new UserInvitationCollection($projectUsers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Master\UserInvitation\UserInvitationResource
     */
    public function store(Request $request)
    {
        // There is no project with this invitation code
        $invitationCode = strtoupper($request->get('invitation_code'));
        $project = Project::where('invitation_code', $invitationCode)->first();
        if (! $project) {
            return response()->json(['message' => 'Invitation code invalid'], 422);
        }

        $user = auth()->user();
        // Check if user already join to the project
        if (ProjectUser::where('user_id', $user->id)->where('project_id', $project->id)->where('joined', true)->first()) {
            return response()->json(['message' => 'You already join to this project'], 422);
        }

        // Check if user already sent a request but not accepted yet
        if (ProjectUser::where('user_id', $user->id)->where('project_id', $project->id)->where('joined', false)->first()) {
            return response()->json(['message' => 'Wait your request to be approved'], 422);
        }

        $projectUser = new ProjectUser;
        $projectUser->project_id = $project->id;
        $projectUser->user_id = $user->id;
        $projectUser->user_name = $user->name;
        $projectUser->user_email = $user->email;
        $projectUser->joined = false;
        $projectUser->request_join_at = now();
        $projectUser->save();

        return new UserInvitationResource($projectUser);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\UserInvitation\UserInvitationResource
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        $projectUser = ProjectUser::findOrFail($id);
        $projectUser->joined = true;
        $projectUser->save();

        $user = User::findOrFail($projectUser->user_id);

        $dbName = env('DB_DATABASE').'_'.strtolower($projectUser->project->code);
        config()->set('database.connections.tenant.database', $dbName);
        DB::connection('tenant')->reconnect();

        $tenantUser = new \App\Model\Master\User;
        $tenantUser->id = $user->id;
        $tenantUser->name = $user->name;
        $tenantUser->first_name = $user->first_name;
        $tenantUser->last_name = $user->last_name;
        $tenantUser->email = $user->email;
        $tenantUser->address = $user->address;
        $tenantUser->phone = $user->phone;
        $tenantUser->save();

        // Add role to new user
        // TODO: make this role dynamic
        $role = Role::findByName('super admin', 'api');
        $tenantUser->assignRole($role);

        DB::commit();

        return new UserInvitationResource($projectUser);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $projectUser = ProjectUser::findOrFail($id);

        $projectUser->delete();

        return response()->json([
            'code' => '200',
            'message' => 'delete success',
        ]);
    }
}
