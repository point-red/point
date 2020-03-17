<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use App\User;
use Illuminate\Http\Request;

class ProjectUserController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Project\Project\DeleteProjectRequest $request
     * @param  int                                                    $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $project = Project::findOrFail($request->get('project_id'));
        $user = User::findOrFail($request->get('user_id'));

        ProjectUser::where('project_id', $project->id)->where('user_id', $user->id)->delete();

        return response()->json([], 204);
    }
}
