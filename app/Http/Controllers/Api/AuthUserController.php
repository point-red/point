<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Master\User\UserResource;
use Illuminate\Http\Request;

class AuthUserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\UserCollection
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $res = [
            'email' => $user->email,
            'name' => $user->name
        ];

        return $res;
    }
}
