<?php

namespace App\Http\Controllers\Web;

use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends WebController
{
    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        session()->put('callback', $request->get('callback'));

        return view('oauth/login');
    }

    public function store(Request $request)
    {
        $usernameLabel = Str::contains($request->username, '@') ? 'email' : 'name';

        $attempt = auth()->guard('web')->attempt([
            $usernameLabel => $request->username,
            'password' => $request->password,
        ]);

        if (! $attempt) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->guard('web')->user();

        $tokenResult = $user->createToken($user->name);

        $data = $user;
        $data->access_token = $tokenResult->accessToken;
        $data->token_type = 'Bearer';
        $data->token_id = $tokenResult->token->id;
        $data->token_expires_in = $tokenResult->token->expires_at->timestamp;

        return Redirect::away('/oauth/login/callback?'
            .'access_token='.$tokenResult->accessToken
            .'&id='.$tokenResult->token->id
            .'&ed='.$tokenResult->token->expires_at);
    }

    public function handleCallback(Request $request)
    {
        return Redirect::away(session()->get('callback').'?'
            .'access_token='.$request->get('access_token')
            .'&id='.$request->get('id')
            .'&ed='.$request->get('ed'));
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle(Request $request)
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        $oAuthUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $oAuthUser->getEmail())->first();
        $fullName = $oAuthUser->getName();
        $fullName = explode(' ', $fullName);
        $firstName = $fullName[0];
        $lastName = '';

        if (count($fullName) > 1) {
            $lastName = $fullName[1];
        }

        if (! $user) {
            return Redirect::away(env('WEBSITE_URL').'/signup?'
                .'email='.$oAuthUser->getEmail()
                .'&name='.$oAuthUser->getNickname()
                .'&first_name='.$firstName
                .'&last_name='.$lastName);
        }

        $tokenResult = $user->createToken($user->name);

        return Redirect::away(session()->get('callback').'?'
            .'access_token='.$tokenResult->accessToken
            .'&id='.$tokenResult->token->id
            .'&ed='.$tokenResult->token->expires_at);
    }
}
