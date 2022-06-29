<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendEmailRequest;
use Illuminate\Support\Facades\Mail;

use App\Mail\CustomEmail;
use App\Model\Project\Project;

class EmailServiceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(SendEmailRequest $request)
    {   
        $project = Project::where('code', $request->header('Tenant'))->first();
        $user = tenant(auth()->user()->id);
        // doesn't allow send custom email with default mail ...@point.red
        if (! $project) {
            return response()->json([
                'message' => 'Cannot send custom email from default email address',
            ], 422);
        }

        Mail::queue(new CustomEmail($project, $user, $request->all()));

        return response()->json([], 204);
    }
}
