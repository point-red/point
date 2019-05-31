<?php

namespace App\Http\Controllers\Api;

use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SendEmailRequest;

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
        $project = Project::join('project_preferences', 'project_preferences.project_id', '=', 'projects.id')
            ->where('code', $request->header('Tenant'))
            ->select('projects.*')
            ->with('preference')
            ->first();

        // doesn't allow send custom email with default mail ...@point.red
        if (! $project || ! $project->preference) {
            return response()->json([
                'message' => 'Cannot send custom email from default email address',
            ], 422);
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
                $message->replyTo($request->get('reply_to'), $request->get('reply_to_name'));
            }
            $message->subject($request->get('subject'));
            $message->setBody($request->get('body'), 'text/html');

            $attachments = $request->get('attachments') ?? [];

            foreach ($attachments as $attachment) {
                if ($attachment['type'] === 'pdf') {
                    $filename = $attachment['filename'] ?? 'untitled.pdf';
                    $file = $this->createPDF($attachment);

                    $message->attachData($file, $filename);
                }
                // TODO excel attachment
            }
        });
    }

    private function createPDF($config)
    {
        // $pdf = PDF::loadHTML($config['html']) don't know why doesn't work
        // https://github.com/barryvdh/laravel-dompdf#using
        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadHTML($config['html'])
            ->setPaper($config['paper'] ?? 'a4', $config['orientation'] ?? 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }
}
