<?php

namespace App\Http\Controllers\Api\Project;

use Illuminate\Http\Request;
use App\Model\ProjectPreference;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;

class ProjectPreferenceController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param $project_id
     * @return ApiResource
     */
    public function show($project_id)
    {
        $projectPreference = ProjectPreference::where('project_id', $project_id)->first();

        $projectPreference->mail_username = $projectPreference->mail_username ? decrypt($projectPreference->mail_username) : null;
        $projectPreference->mail_password = $projectPreference->mail_password ? decrypt($projectPreference->mail_password) : null;
        $projectPreference->mail_domain = $projectPreference->mail_domain ? decrypt($projectPreference->mail_domain) : null;
        $projectPreference->mail_secret = $projectPreference->mail_secret ? decrypt($projectPreference->mail_secret) : null;

        return new ApiResource($projectPreference);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $project_id
     * @param $id
     * @return void
     */
    public function update(Request $request, $project_id)
    {
        $projectPreference = ProjectPreference::where('project_id', $project_id)->first();
        if (! $projectPreference) {
            $projectPreference = new ProjectPreference;
            $projectPreference->project_id = $project_id;
            // TODO validate project_id is exist
        }

        $projectPreference->mail_driver = $request->get('mail_driver');
        $projectPreference->mail_host = $request->get('mail_host');
        $projectPreference->mail_username = empty($request->get('mail_username')) ? null : encrypt($request->get('mail_username'));
        $projectPreference->mail_password = empty($request->get('mail_password')) ? null : encrypt($request->get('mail_password'));
        $projectPreference->mail_from_name = $request->get('mail_from_name');
        $projectPreference->mail_from_address = $request->get('mail_from_address');
        $projectPreference->mail_port = $request->get('mail_port');
        $projectPreference->mail_encryption = $request->get('mail_encryption');
        $projectPreference->mail_domain = empty($request->get('mail_domain')) ? null : encrypt($request->get('mail_domain'));
        $projectPreference->mail_secret = empty($request->get('mail_secret')) ? null : encrypt($request->get('mail_secret'));
        
        $projectPreference->save();
    }
}
