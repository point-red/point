<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Model\Master\User as TenantUser;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Master\User\UserResource;
use App\Http\Resources\Master\User\UserCollection;
use App\Http\Requests\Master\User\StoreUserRequest;
use App\Http\Requests\Master\User\UpdateUserRequest;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\Master\User\UserCollection
     */
    public function index(Request $request)
    {
        return new UserCollection(TenantUser::with('roles')->paginate($limit ?? 100));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\User\StoreUserRequest $request
     *
     * @return \App\Http\Resources\Master\User\UserResource
     */
    public function store(StoreUserRequest $request)
    {
        $tenantUser = new TenantUser;
        $tenantUser->name = $request->name;
        $tenantUser->email = $request->email;
        $tenantUser->password = bcrypt($request->password);
        $tenantUser->save();

        return new UserResource($tenantUser);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\User\UserResource
     */
    public function show($id)
    {
        return new UserResource(TenantUser::with('roles')->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \App\Http\Resources\Master\User\UserResource
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $tenantUser = TenantUser::findOrFail($id);
        $tenantUser->name = $request->name;
        $tenantUser->email = $request->email;
        $tenantUser->save();

        return new UserResource($tenantUser);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        TenantUser::findOrFail($id)->delete();

        return response(null, 204);
    }
}
