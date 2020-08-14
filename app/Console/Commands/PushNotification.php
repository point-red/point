<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push-notification {title} {body} {click_action} {token*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tokens = $this->argument('token');
        $title = $this->argument('title');
        $body = $this->argument('body');
        $clickAction = $this->argument('click_action');

        self::send($tokens, $title, $body, $clickAction);
    }

    private static function send($tokens, $title, $body, $clickAction)
    {
        $msg = [
            'title'     => $title,
            'body'      => $body,
            'sound'     => 'default',
            'click_action' => $clickAction,
        ];

        $fields = [
            'registration_ids'    => $tokens,
            'notification'        => $msg,
            'priority'            => 'high',
        ];

        $headers = [
            'Authorization: key='.env('FIREBASE_SERVER_KEY'),
            'Content-Type: application/json',
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_exec($curl);
        curl_close($curl);
    }
}
