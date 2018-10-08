<?php

namespace App\Http\Controllers\Api\Auth;

use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

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
            'password' => $request->password,
        ]);

        if (! $attempt) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->guard('web')->user();

        $tokenResult = $user->createToken($user->name);

        $response = $user;
        $response->access_token = $tokenResult->accessToken;
        $response->token_type = 'Bearer';
        $response->token_id = $tokenResult->token->id;
        $response->token_expires_at = $tokenResult->token->expires_at;

        if ($request->header('Tenant')) {
            $project = Project::where('code', $request->header('Tenant'))->first();

            if ($project) {
                $response->tenant_code = $project->code;
                $response->tenant_name = $project->name;
                $response->permissions = tenant($user->id)->getPermissions();
            }
        }

        return response()->json([
            'data' => $response,
        ]);
    }
}
