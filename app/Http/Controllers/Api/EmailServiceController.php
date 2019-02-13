<?php

namespace App\Http\Controllers\Api;

use App\Model\Project\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class EmailServiceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        $project = Project::join('project_preferences', 'project_preferences.project_id', '=', 'projects.id')
            ->where('code', $request->header('Tenant'))
            ->select('projects.*')
            ->with('preference')
            ->first();

        // doesn't allow send custom email with default mail ...@point.red
        if (!$project->preference) {
            return response()->json([
                'message' => 'Cannot send custom email from default email address'
            ]);
        }

        Mail::send([], [], function ($message) use ($request) {
            $message->to($request->get('to'));
            if ($request->has('cc')) {
                $message->cc($request->get('cc'));
            }
            if ($request->has('bcc')) {
                $message->bcc($request->get('bcc'));
            }
            if ($request->has('reply_to')) {
                $message->replyTo($request->get('reply_to'));
            }
            $message->subject($request->get('subject'));
            $message->setBody($request->get('body'), 'text/html');
        });
    }
}
