<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\UserWarehouse\AttachRequest;
use App\Http\Requests\Master\UserWarehouse\SetDefaultRequest;
use App\Http\Resources\ApiResource;
use App\Model\Master\User;
use App\Model\Master\User as TenantUser;

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

    public function updateDefault(SetDefaultRequest $request)
    {
        $tenantUser = TenantUser::findOrFail($request->get('user_id'));

        if ($request->get('is_default') == true) {
            foreach ($tenantUser->warehouses as $warehouse) {
                $warehouse->pivot->is_default = false;
                $warehouse->pivot->save();
            }
        }

        $tenantUser->warehouses()->updateExistingPivot($request->get('warehouse_id'), [
            'is_default' => $request->get('is_default'),
        ], false);

        return new ApiResource($tenantUser);
    }
}
