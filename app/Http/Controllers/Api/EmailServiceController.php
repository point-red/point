<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendEmailRequest;
use App\Model\Project\Project;
use Illuminate\Support\Facades\Mail;

class EmailServiceController extends Controller
{
    private $tenant;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(SendEmailRequest $request)
    {   
        $project = Project::where('code', $request->header('Tenant'))->first();
        // doesn't allow send custom email with default mail ...@point.red
        if (! $project) {
            return response()->json([
                'message' => 'Cannot send custom email from default email address',
            ], 422);
        }

        $this->tenant = $project;

        Mail::send([], [], function ($message) use ($request) {
            $user = tenant(auth()->user()->id);

            $message->from($user->email, $user->getFullNameAttribute());
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
            $message->setBody($request->get('body'), 'text/plain');

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

        return response()->json([], 204);
    }

    private function createPDF($config)
    {
        if (!isset($config['html']) && (!isset($config['view']) && !isset($config['view_data']))) {
            return response()->json([
                'message' => 'Cannot send custom email, html / view must filled',
            ], 422);
        }

        // $pdf = PDF::loadHTML($config['html']) don't know why doesn't work
        // https://github.com/barryvdh/laravel-dompdf#using
        $pdf = app()->make('dompdf.wrapper')
            ->setPaper($config['paper'] ?? 'a4', $config['orientation'] ?? 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->setWarnings(false);
        
        if(isset($config['html'])) $pdf->loadHTML($config['html']);

        if(isset($config['view'])){
            $viewData = [
                'logo' => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('/img/logo.png'))),
                'tenant' => $this->tenant,
            ];
            foreach ($config['view_data'] as $key => $value) {
                $viewData[$key] = $value;
            }

            $pdf->loadView('emails.' . $config['view'], $viewData);
        }

        return $pdf->output();
    }
}
