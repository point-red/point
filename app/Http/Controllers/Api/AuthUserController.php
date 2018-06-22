<?php

namespace App\Http\Controllers\Api;

use App\Model\Project\Project;
use Illuminate\Http\Request;

class AuthUserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
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

        if ($request->get('tenant_code')) {
            $project = Project::where('code', $request->get('tenant_code'))->first();

            if ($project) {
                $userData['data'] = array_merge($userData['data'], [
                    'tenant_code' => $project->code,
                    'tenant_name' => $project->name,
                ]);
            }
        }

        return $userData;
    }
}
