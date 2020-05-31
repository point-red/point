<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Package;
use App\Model\Project\Project;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $packages = Package::from('packages as ' . Package::$alias)->where('is_active', true);

        $packages = pagination($packages, $request->input('limit'));

        return new ApiCollection($packages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
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

    public function subscribe(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $project = Project::findOrFail($request->get('project_id'));

        $project->package_id = $package->id;
        $project->save();

        return new ApiResource($project);
    }

    public function unsubscribe(Request $request, $id)
    {
        $project = Project::findOrFail($request->get('project_id'));

        $project->package_id = 1;
        $project->save();

        return new ApiResource($project);
    }
}
