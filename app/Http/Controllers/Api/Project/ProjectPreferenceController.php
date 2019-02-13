<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\ProjectPreference;
use Illuminate\Http\Request;

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
        return new ApiResource(ProjectPreference::where('project_id', $project_id)->first());
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
        if (!$projectPreference) {
            $projectPreference = new ProjectPreference;
            $projectPreference->project_id = $project_id;
            // TODO validate project_id is exist
        }

        $projectPreference->mail_driver = $request->get('mail_driver');
        $projectPreference->mail_host = $request->get('mail_host');
        $projectPreference->mail_username = $request->get('mail_username');
        $projectPreference->mail_password = $request->get('mail_password');
        $projectPreference->mail_from_name = $request->get('mail_from_name');
        $projectPreference->mail_from_address = $request->get('mail_from_address');
        $projectPreference->mail_port = $request->get('mail_port');
        $projectPreference->mail_encryption = $request->get('mail_encryption');
        $projectPreference->mail_secret = $request->get('mail_secret');
        $projectPreference->save();
    }
}
