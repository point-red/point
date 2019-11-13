<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Model\Project\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Passport\Token;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param LoginRequest $request
     *
     * @return Response
     */
    public function index(LoginRequest $request)
    {
        // check username for login is name or email
        $usernameLabel = Str::contains($request->username, '@') ? 'email' : 'name';

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

        $tokenResult->token->is_mobile = $request->input('is_mobile') ?? null;
        $tokenResult->token->os_name = $request->input('os_name') ?? null;
        $tokenResult->token->os_version = $request->input('os_version') ?? null;
        $tokenResult->token->browser_name = $request->input('browser_name') ?? null;
        $tokenResult->token->browser_version = $request->input('browser_version') ?? null;
        $tokenResult->token->mobile_vendor = $request->input('mobile_vendor') ?? null;
        $tokenResult->token->mobile_model = $request->input('mobile_model') ?? null;
        $tokenResult->token->engine_name = $request->input('engine_name') ?? null;
        $tokenResult->token->engine_version = $request->input('engine_version') ?? null;
        $tokenResult->token->save();

        $response = $user;
        $response->access_token = $tokenResult->accessToken;
        $response->token_type = 'Bearer';
        $response->token_id = $tokenResult->token->id;
        $response->token_expires_in = $tokenResult->token->expires_at->timestamp;

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
