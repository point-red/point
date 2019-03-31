<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Model\Auth\Role;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $roles = Role::eloquentFilter($request);

        $roles = pagination($roles, $request->get('limit'));

        return new ApiCollection($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $role = new Role;
        $role->name = $request->get('name');
        $role->guard_name = 'api';
        $role->save();

        return new ApiResource($role);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return ApiResource
     */
    public function show($id)
    {
        return new ApiResource(Role::findOrFail($id));
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
