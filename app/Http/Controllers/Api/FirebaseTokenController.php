<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ApiResource;
use App\Model\FirebaseToken;
use App\Model\Project\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FirebaseTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $project = Project::where('code', $request->header('Tenant'))->first();

        $firebaseToken = new FirebaseToken;

        if (!FirebaseToken::where('user_id', auth()->user()->id)
            ->where('project_id', optional($project)->id)
            ->where('token', $request->get('token'))
            ->first()) {
            $firebaseToken->user_id = auth()->user()->id;
            $firebaseToken->project_id = optional($project)->id;
            $firebaseToken->token = $request->get('token');
            $firebaseToken->save();
        }

        return new ApiResource($firebaseToken);
    }
}
