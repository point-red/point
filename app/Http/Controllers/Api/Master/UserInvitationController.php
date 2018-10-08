<?php

namespace App\Http\Controllers\Api\Master;

use App\User;
use App\Model\Auth\Role;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Master\UserInvitation\UserInvitationResource;
use App\Http\Resources\Master\UserInvitation\UserInvitationCollection;
use App\Http\Requests\Master\UserInvitation\StoreUserInvitationRequest;

class UserInvitationController extends Controller
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
        $project = Project::where('code', $request->header('Tenant'))->first();

        $projectUsers = ProjectUser::where('project_id', $project->id)->where('joined', false)->get();

        return new UserInvitationCollection($projectUsers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\UserInvitation\StoreUserInvitationRequest $request
     *
     * @return \App\Http\Resources\Master\UserInvitation\UserInvitationResource
     */
    public function store(StoreUserInvitationRequest $request)
    {
        // Check if invited user already registered
        $user = User::where('email', $request->get('user_email'))->first();
        $project = Project::where('code', $request->header('Tenant'))->first();
        if ($user) {
            // If user registered
            $projectUser = new ProjectUser;
            $projectUser->project_id = $project->id;
            $projectUser->user_id = $user->id;
            $projectUser->user_name = $request->get('user_name');
            $projectUser->user_email = $request->get('user_email');
            $projectUser->joined = false;
            $projectUser->save();
        } else {
            // If user not registered
            $projectUser = new ProjectUser;
            $projectUser->project_id = $project->id;
            $projectUser->user_id = null;
            $projectUser->user_name = $request->get('user_name');
            $projectUser->user_email = $request->get('user_email');
            $projectUser->joined = false;
            $projectUser->save();
        }

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
        $projectUser = ProjectUser::findOrFail($id);
        $projectUser->joined = true;
        $projectUser->save();

        $user = User::findOrFail($request->get('user_id'));

        $dbName = 'point_'.strtolower($projectUser->project->code);
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
        //
    }
}
