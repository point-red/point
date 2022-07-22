<?php

namespace App\Services\Google;

use App\Model\OauthUserToken;

class Google
{
    private static $oauthScope = 'https://www.googleapis.com/auth/drive.file';
    
    /**
     * Setup google client
     *
     * @return \Google_Client
     */
    public static function client()
    {
        $client = self::initClient();

        self::setAccessToken($client);
        
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken();
            if ($refreshToken) {
                self::refreshAccessToken($client);
            }
        }

        return $client;
    }

    /**
     * Setup google client
     *
     * @return \Google_Client
     */
    private static function initClient()
    {
        $oauthClientId = config('services.google.client_id');
        $oauthClientSecret = config('services.google.client_secret');
        $oauthRedirectUri = 'https://' . config('app.tenant_domain') . '/oauth/google/callback';
    
        // $client = new \Google\Client(); // newer version use this
        $client = new \Google_Client();

        $client->setRedirectUri($oauthRedirectUri);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setAuthConfig([
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "project_id" => config('services.google.project_id'),
            "client_id" => $oauthClientId,
            "client_secret" => $oauthClientSecret
        ]);
        $client->setScopes(self::$oauthScope);

        return $client;
    }

    /**
     * Get user access / refresh token
     *
     * @return \App\Model\OauthUserToken|null
     */
    private static function getStoredToken()
    {
        return \App\Model\OauthUserToken::where('user_id', auth()->id())
            ->where('provider', 'google')
            ->where('scope', self::$oauthScope)
            ->first();
    }

    /**
     * Get stored access token and set to google client.
     *
     * @return void
     */
    private static function setAccessToken(\Google_Client $client)
    {
        $storedToken = self::getStoredToken();

        if ($storedToken) {
            $client->setAccessToken($storedToken);
        }
    }

    /**
     * Get new access token from refresh token if possible.
     * Save or replace existing access token if new access token generated.
     * Delete existing refresh token if failed to get new access token.
     *
     * @return void
     */
    private static function refreshAccessToken(\Google_Client $client)
    {
        // Refresh the token if possible, else fetch a new one.
        $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

        // Unable to get new access token. Refresh token is invalid or expired.
        if (isset($newToken['error']) || ! isset($newToken['created'])) {
            self::deleteUnusedToken();
            return;
        }

        self::updateOrCreateUserToken($newToken);
    }

    /**
     * Update existing user token or create a new one.
     *
     * @return void
     */
    private static function updateOrCreateUserToken(array $newToken)
    {
        $created = \Carbon\Carbon::createFromTimestamp($newToken['created']);
        if ($storedToken = self::getStoredToken()) {
            // refresh token exist, update access token
            $storedToken->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => $created->addSeconds($newToken['expires_in']),
            ]);

            return;
        }

        // refresh token doesnt exist, save access and refresh token
        OauthUserToken::create([
            'provider' => 'google',
            'access_token' => $newToken['access_token'],
            'refresh_token' => $newToken['refresh_token'],
            'expires_at' => $created->addSeconds($newToken['expires_in']),
            'scope' => $newToken['scope'],
        ]);
    }

    /**
     * Delete expired refresh token. Make sure only ony token per user per scope.
     *
     * @return void
     */
    private static function deleteUnusedToken()
    {
        \App\Model\OauthUserToken::where('user_id', auth()->id())
            ->where('provider', 'google')
            ->where('scope', self::$oauthScope)
            ->delete();
    }
}
