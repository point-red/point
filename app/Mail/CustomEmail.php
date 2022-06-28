<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Model\Setting\SettingLogo;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $request;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($tenant, $user, $request)
    {
        $this->tenant = $tenant;
        $this->request = (object) $request;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = $this->user;
        $request = $this->request;

        $this->from($user->email, $user->getFullNameAttribute());
        $this->to($request->to);

        if (optional($request)->cc) {
            $this->cc($request->cc);
        }
        if (optional($request)->bcc) {
            $this->bcc($request->bcc);
        }
        if (optional($request)->reply_to) {
            $this->replyTo($request->reply_to, $request->reply_to_name);
        }

        $this->subject($request->subject);
        $this->view('emails.custom-email', ['body' => $request->body]);

        $attachments = $request->attachments ?? [];
        foreach ($attachments as $attachment) {
            if ($attachment['type'] === 'pdf') {
                $filename = $attachment['filename'] ?? 'untitled.pdf';
                $file = $this->createPDF($attachment);

                $this->attachData($file, $filename);
            }
            // TODO excel attachment
        }

        return $this;

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
            $settingLogo = SettingLogo::orderBy("id", 'desc')->first();
            
            $logo = optional($settingLogo)->public_url 
                ? $settingLogo->public_url 
                : 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('/img/logo.png')));

            $viewData = [
                'logo' => $logo,
                'draftimg' => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('/img/draft-watermark.png'))),
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
