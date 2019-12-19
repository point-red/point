<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\UserWarehouse\AttachRequest;
use App\Http\Resources\ApiResource;
use App\Model\Master\User;

class UserWarehouseController extends ApiController
{
    public function attach(AttachRequest $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        $user->warehouses()->syncWithoutDetaching($request->get('warehouse_id'));

        return new ApiResource($user);
    }

    public function detach(AttachRequest $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        $user->warehouses()->detach($request->get('warehouse_id'));

        return new ApiResource($user);
    }
}
