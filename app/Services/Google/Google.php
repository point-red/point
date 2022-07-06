<?php

namespace App\Services\Google;

class Google
{
    public static function client()
    {
        $oauthClientId = config('services.google.client_id');
        $oauthClientSecret = config('services.google.client_secret');
        $oauthRedirectUri = config('services.google.redirect');
    
        // $client = new \Google\Client(); // newer version use this
        $client = new \Google_Client();
    
        // $client->setClientId($oauthClientId);
        // $client->setClientSecret($oauthClientSecret);
        $client->setRedirectUri($oauthRedirectUri);
        $client->setAuthConfig([
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "project_id" => "point-test-gdrive",
            "client_id" => $oauthClientId,
            "client_secret" => $oauthClientSecret
        ]);
        $client->setScopes("https://www.googleapis.com/auth/drive.file");

        // get user access / refresh token
        // $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                info('[GOOGLE] should get new refresh token');
                // Request authorization from the user.
                // $authUrl = $client->createAuthUrl();
                // $authCode = trim(fgets(STDIN));
    
                // // Exchange authorization code for an access token.
                // $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                // $client->setAccessToken($accessToken);
            }
        }

        return $client;
    }
}