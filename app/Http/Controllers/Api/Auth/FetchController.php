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
                'message' => 'Unauthenticated',
            ], 401);
        }

        $response = $request->user();
        if ($request->header('Tenant')) {
            $project = Project::where('code', $request->header('Tenant'))->first();

            if ($project) {
                $response->tenant_code = $project->code;
                $response->tenant_name = $project->name;
                $response->tenant_address = $project->address;
                $response->tenant_phone = $project->phone;
                $response->tenant_owner_id = $project->owner_id;
                $response->is_owner = $project->owner_id == $request->user()->id;
                $response->permissions = tenant($request->user()->id)->getPermissions();
                $response->branches = tenant($request->user()->id)->branches;
                $response->branch = null;
                $response->warehouses = tenant($request->user()->id)->warehouses;
                $response->warehouse = null;
                foreach ($response->branches as $branch) {
                    if ($branch->pivot->is_default) {
                        $response->branch = $branch;
                    }
                }
                foreach ($response->warehouses as $warehouse) {
                    if ($warehouse->pivot->is_default) {
                        $response->warehouse = $warehouse;
                    }
                }
            }
        }

        return response()->json([
            'data' => $response,
        ]);
    }
}
