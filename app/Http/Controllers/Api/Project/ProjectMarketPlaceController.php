<?php

namespace App\Http\Controllers\Api\Project;

use Illuminate\Http\Request;
use App\Model\ProjectMarketPlace;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;

class ProjectMarketPlaceController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param $project_id
     * @return ApiResource
     */
    public function show($project_id)
    {
        $projectMarketPlace = ProjectMarketPlace::where('project_id', $project_id)->first();

        return new ApiResource($projectMarketPlace);
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
        $projectMarketPlace = ProjectMarketPlace::where('project_id', $project_id)->first();
        
        if (!$projectMarketPlace) {
            $projectMarketPlace = new ProjectMarketPlace;
            $projectMarketPlace->project_id = $project_id;
            // TODO validate project_id is exist
        }

        $projectMarketPlace->joined = $request->get('joined');
        $projectMarketPlace->save();
    }
}
