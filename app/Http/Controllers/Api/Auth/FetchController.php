<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Model\Project\Project;
use Illuminate\Http\Request;

class FetchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (! $request->user()) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $response = $request->user();
        if ($request->header('Tenant')) {
            $project = Project::where('code', $request->header('Tenant'))->first();

            if ($project) {
                $response->tenant_code = $project->code;
                $response->tenant_name = $project->name;
                $response->permissions = tenant($request->user()->id)->getPermissions();
            }
        }

        return response()->json([
            'data' => $response
        ]);
    }
}
