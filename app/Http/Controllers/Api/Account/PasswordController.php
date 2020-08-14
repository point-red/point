<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Password\UpdatePasswordRequest;
use App\Http\Resources\Account\UserResource;
use App\User;
use Illuminate\Support\Facades\DB;

class PasswordController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Account\Password\UpdatePasswordRequest $request
     * @param  int                                                      $id
     *
     * @return \App\Http\Resources\Account\UserResource
     */
    public function update(UpdatePasswordRequest $request, $id)
    {
        DB::beginTransaction();

        $user = User::findOrFail($id);
        $user->password = bcrypt($request->get('password'));
        $user->save();

        DB::commit();

        return new UserResource($user);
    }
}
