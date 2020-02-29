<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Auth\Role;
use App\Model\Master\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

        foreach ($roles as $role) {
            $role->users = User::whereHas('roles', function (Builder $query) use ($role) {
                $query->where('role_id', '=', $role->id);
            })->get();
        }

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
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->name = $request->get('name');
        $role->guard_name = 'api';
        $role->save();

        return new ApiResource($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json([], 204);
    }
}
