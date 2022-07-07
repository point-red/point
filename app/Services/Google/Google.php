<?php

namespace App\Services\Google;

use App\Model\OauthUserToken;

class Google
{
    public static function client()
    {
        $oauthClientId = config('services.google.client_id');
        $oauthClientSecret = config('services.google.client_secret');
        $oauthScope = "https://www.googleapis.com/auth/drive.file";
    
        // $client = new \Google\Client(); // newer version use this
        $client = new \Google_Client();

        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setAuthConfig([
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "project_id" => "point-test-gdrive",
            "client_id" => $oauthClientId,
            "client_secret" => $oauthClientSecret
        ]);
        $client->setScopes($oauthScope);

        // get user access / refresh token
        $storedToken = \App\Model\OauthUserToken::where('user_id', auth()->id())
            ->where('provider', 'google')
            ->where('scope', $oauthScope)
            ->first();

        if ($storedToken) {
            $client->setAccessToken($storedToken);
        }

        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $created = \Carbon\Carbon::createFromTimestamp($newToken['created']);
                if ($storedToken) {
                    $storedToken->update([
                        'access_token' => $newToken['access_token'],
                        'expires_at' => $created->addSeconds($newToken['expires_in']),
                    ]);
                } else {
                    OauthUserToken::create([
                        'provider' => 'google',
                        'access_token' => $newToken['access_token'],
                        'refresh_token' => $newToken['refresh_token'],
                        'expires_at' => $created->addSeconds($newToken['expires_in']),
                        'scope' => $newToken['scope'],
                    ]);
                }
            }
        }

        return $client;
    }
}