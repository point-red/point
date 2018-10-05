<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LoginRequest $request)
    {
        // check username for login is name or email
        $usernameLabel = str_contains($request->username, '@') ? 'email' : 'name';

        $attempt = auth()->guard('web')->attempt([
            $usernameLabel => $request->username,
            'password' => $request->password
        ]);

        if (!$attempt) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->guard('web')->user();

        $token = $user->createToken('Token Name')->accessToken;

        $user->access_token = $token;

        $response = $user;

        return response()->json($response);
    }
}
