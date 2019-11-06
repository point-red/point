<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Master\User\StoreUserRequest;
use App\Http\Resources\ApiResource;
use App\User;

class RegisterController extends ApiController
{
    public function store(StoreUserRequest $request)
    {
        $emailConfirmationCode = substr(encrypt($request->input('email')), 0, 30);

        $user = new User;
        $user->name = $request->username;
        $user->email = $request->email;
        $user->email_confirmation_code = $emailConfirmationCode;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->save();

        return new ApiResource($user);
    }
}
