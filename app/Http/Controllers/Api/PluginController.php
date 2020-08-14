<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Plugin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $plugins = Plugin::from('plugins as '.Plugin::$alias);

        $plugins = pagination($plugins, $request->input('limit'));

        return new ApiCollection($plugins);
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
        $plugin = Plugin::findOrFail($id);
        $plugin->projects()->attach($request->get('project_id'), [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expired_date' => date('Y-m-t 23:59:59'),
        ]);

        return new ApiResource($plugin);
    }

    public function unsubscribe(Request $request, $id)
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->projects()->detach($request->get('project_id'));

        return new ApiResource($plugin);
    }
}
