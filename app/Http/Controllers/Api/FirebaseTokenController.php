<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Model\FirebaseToken;
use App\Model\Project\Project;
use Illuminate\Http\Request;

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
        $firebaseToken->user_id = auth()->user()->id;
        $firebaseToken->project_id = optional($project)->id;
        $firebaseToken->token = $request->get('token');

        // Upsert: update if exists, otherwise insert
        $existing = FirebaseToken::where('user_id', $firebaseToken->user_id)
            ->where('project_id', $firebaseToken->project_id)
            ->first();

        if ($existing) {
            $existing->token = $firebaseToken->token;
            $existing->save();
            $firebaseToken = $existing;
        } else {
            $firebaseToken->save();
        }

        return new ApiResource($firebaseToken);
    }
}
