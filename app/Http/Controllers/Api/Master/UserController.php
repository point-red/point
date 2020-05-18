<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\User\StoreUserRequest;
use App\Http\Requests\Master\User\UpdateUserRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\User as TenantUser;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $users = TenantUser::eloquentFilter($request);

        // filter users by ?permission query string
        if ($request->has('permission')) {
            $users->permission($request->permission);
        }

        $users = pagination($users, $request->get('limit'));

        return new ApiCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return ApiResource
     */
    public function store(StoreUserRequest $request)
    {
        $tenantUser = new TenantUser;
        $tenantUser->name = $request->name;
        $tenantUser->email = $request->email;
        $tenantUser->password = bcrypt($request->password);
        $tenantUser->save();

        return new ApiResource($tenantUser);
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
        $user = TenantUser::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $tenantUser = TenantUser::findOrFail($id);
        $tenantUser->first_name = $request->first_name;
        $tenantUser->last_name = $request->last_name;
        $tenantUser->address = $request->address;
        $tenantUser->phone = $request->phone;
        $tenantUser->email = $request->email;
        $tenantUser->save();

        return new ApiResource($tenantUser);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        TenantUser::findOrFail($id)->delete();

        $project = Project::where('code', $request->header('Tenant'))->first();

        ProjectUser::where('user_id', $id)->where('project_id', $project->id)->delete();

        return response(null, 204);
    }
}
