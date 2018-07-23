<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Requests\Master\UserInvitation\StoreUserInvitationRequest;
use App\Http\Resources\Master\UserInvitation\UserInvitationCollection;
use App\Http\Resources\Master\UserInvitation\UserInvitationResource;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        $projectUsers = ProjectUser::where('project_id', $project->id)->get();

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
        $user = User::where('email', $request->get('email'))->first();
        $project = Project::where('code', $request->header('Tenant'))->first();
        if ($user) {
            // If user registered
            $projectUser = new ProjectUser;
            $projectUser->project_id = $project->id;
            $projectUser->user_id = $user->id;
            $projectUser->user_name = $request->get('name');
            $projectUser->user_email = $request->get('email');
            $projectUser->joined = false;
            $projectUser->save();
        } else {
            // If user not registered
            $projectUser = new ProjectUser;
            $projectUser->project_id = $project->id;
            $projectUser->user_id = null;
            $projectUser->user_name = $request->get('name');
            $projectUser->user_email = $request->get('email');
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
