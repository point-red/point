<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\User;

class RegisterController extends Controller
{
    public function register(StoreUserRequest $request) {
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

    }
}
