<?php

namespace App\Mail;

use App\Model\Project\Project;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    private $project;
    private $user;
    private $name;

    /**
     * Create a new message instance.
     *
     * @param Project $project
     * @param User $user
     * @param $name
     */
    public function __construct(Project $project, User $user, $name)
    {
        $this->project = $project;
        $this->user = $user;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(strtoupper($this->project->name).' invite you to join project')
            ->view('emails.user.user-invitation')
            ->with([
                'project' => $this->project,
                'user' => $this->user,
                'name' => $this->name,
            ]);
    }
}
