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
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'address' => $user->address,
                'phone' => $user->phone,
            ],
        ];

        if ($request->get('tenant_code')) {
            $project = Project::where('code', $request->get('tenant_code'))->first();

            if ($project) {
                $userData['data'] = array_merge($userData['data'], [
                    'tenant_code' => $project->code,
                    'tenant_name' => $project->name,
                    'permissions' => tenant($user->id)->getPermissions(),
                ]);
            }
        }

        return $userData;
    }
}
