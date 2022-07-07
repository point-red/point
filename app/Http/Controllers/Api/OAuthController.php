<?php

namespace App\Http\Controllers\Api;

use App\Services\Google\Google;
use App\Model\OauthUserToken;
use Illuminate\Http\Request;

class OAuthController extends ApiController
{
    /**
     * Check if user has authorized their google drive account
     * Return auth url if not yet authorized or refresh
     * token expired.
     */
    public function requestGoogleDrive(Request $request)
    {
        $client = Google::client();
        
        $oauthRedirectUri = config('app.url_web') . '/oauth/google/callback';
        $client->setRedirectUri($oauthRedirectUri);

        return [
            'authenticated' => !$client->isAccessTokenExpired(),
            'auth_url' => $client->createAuthUrl(),
        ];
    }

    /**
     * Receive google auth code from frontend, use the auth code
     * to get access and refresh token and save to database
     * as user's oauth token.
     * 
     * @return void
     */
    public function storeGoogleAccessToken(Request $request)
    {

        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $client = Google::client();

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($validated['code']);
        $client->setAccessToken($accessToken);
        $created = \Carbon\Carbon::createFromTimestamp($accessToken['created']);
        OauthUserToken::create([
            'provider' => 'google',
            'access_token' => $accessToken['access_token'],
            'refresh_token' => $accessToken['refresh_token'],
            'expires_at' => $created->addSeconds($accessToken['expires_in']),
            'scope' => $accessToken['scope'],
        ]);
    }

    /**
     * Remove user's google drive oauth token
     * 
     * @return void
     */
    public function unlinkGoogleDrive()
    {
        OauthUserToken::where('user_id', auth()->id())
            ->where('provider', 'google')
            ->where('scope', 'https://www.googleapis.com/auth/drive.file')
            ->delete();
    }
}
