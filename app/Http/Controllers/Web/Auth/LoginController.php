<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Web\WebController;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends WebController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGithub(Request $request)
    {
        session()->put('callback', $request->get('callback'));

        return Socialite::driver('github')->stateless()->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGithubCallback(Request $request)
    {
        $oAuthUser = Socialite::driver('github')->stateless()->user();

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

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle(Request $request)
    {
        session()->put('callback', $request->get('callback'));

        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
