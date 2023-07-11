<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;

class ExportNotificationMail extends Mailable
{
    use Queueable;

    public $fileName;
    public $path;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $fileName, $path)
    {
        $this->fileName = $fileName;
        $this->path = $path;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->to($this->user->email);

        $this->subject('Export ' . $this->fileName);
        $this->view('emails.custom-email', ['body' => $this->fileName]);

        $this->attachData(Storage::disk(env('STORAGE_DISK'))->get($this->path), $this->fileName . '.xlsx');

        return $this;
    }
}
