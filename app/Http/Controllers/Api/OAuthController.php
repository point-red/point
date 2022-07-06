<?php

namespace App\Http\Controllers\Api;

use App\Services\Google\Google;

class OAuthController extends ApiController
{
    public function requestGoogleDrive()
    {
        $client = Google::client();

        if ($client->isAccessTokenExpired()) {
            return [
                'authenticated' => false,
                'auth_url' => $client->createAuthUrl(),
            ];
        }
        return ['authenticated' => true];
    }
}
