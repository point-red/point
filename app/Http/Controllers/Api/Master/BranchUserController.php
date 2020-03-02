<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\BranchUser\AttachRequest;
use App\Http\Requests\Master\BranchUser\SetDefaultRequest;
use App\Http\Resources\ApiResource;
use App\Model\Master\User as TenantUser;

class BranchUserController extends ApiController
{
    public function attach(AttachRequest $request)
    {
        $tenantUser = TenantUser::findOrFail($request->get('user_id'));
        $tenantUser->branches()->syncWithoutDetaching($request->get('branch_id'));

        return new ApiResource($tenantUser);
    }

    public function detach(AttachRequest $request)
    {
        $tenantUser = TenantUser::findOrFail($request->get('user_id'));
        $tenantUser->branches()->detach($request->get('branch_id'));

        return new ApiResource($tenantUser);
    }

    public function updateDefault(SetDefaultRequest $request)
    {
        $tenantUser = TenantUser::findOrFail($request->get('user_id'));

        if ($request->get('is_default') == true) {
            foreach ($tenantUser->branches as $branch) {
                $branch->pivot->is_default = false;
                $branch->pivot->save();
            }
        }

        $tenantUser->branches()->updateExistingPivot($request->get('branch_id'), [
            'is_default' => $request->get('is_default')
        ], false);

        return new ApiResource($tenantUser);
    }
}
