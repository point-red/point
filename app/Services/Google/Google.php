<?php

class Google
{
    public static function client()
    {
        $oauthClientId = config('services.google.client_id');
        $oauthClientSecret = config('services.google.client_secret');
    
        // $client = new \Google\Client(); // newer version use this
        $client = new \Google_Client();
    
        $client->setClientId($oauthClientId);
        $client->setClientSecret($oauthClientSecret);

        // get user access / refresh token

        return $client;
    }
}