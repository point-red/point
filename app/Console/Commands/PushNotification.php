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
    protected $signature = 'push-notification {token}';

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
        $tokens = [];
        array_push($tokens, $this->argument('token'));
        PushNotification::send($tokens);
    }

    private static function send($tokens)
    {
        $msg = [
            'title'     => 'Test Notification',
            'body'      => 'this is only notification testing',
            'sound'     => 'default',
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        info($result);
    }
}
