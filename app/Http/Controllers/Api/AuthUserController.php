<?php

namespace App\Http\Controllers\Api;

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

        $userData = [
            'data' => [
                'email' => $user->email,
                'name' => $user->name,
            ],
        ];

        return $userData;
    }
}
