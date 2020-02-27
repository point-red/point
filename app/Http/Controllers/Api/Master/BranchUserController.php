<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Master\BranchUser\AttachRequest;
use App\Http\Resources\ApiResource;
use App\Model\Master\User;

class BranchUserController extends ApiController
{
    public function attach(AttachRequest $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        $user->branches()->syncWithoutDetaching($request->get('branch_id'));

        return new ApiResource($user);
    }

    public function detach(AttachRequest $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        $user->branches()->detach($request->get('branch_id'));

        return new ApiResource($user);
    }
}
