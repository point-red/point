<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'tenant';
    public $timestamps = false;

    private $user_id;
    private $project_id;
    private $message;
    private $link;
    private $status;

    public function __construct($user_id, $project_id, $message, $link, $status)
    {
        $this->user_id = $user_id;
        $this->project_id = $project_id;
        $this->message = $message;
        $this->link = $link;
        $this->status = $status;
    }
}
