<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::namespace('Web')->group(function () {
    Route::view('/', 'welcome');

    Route::get('/download', 'CloudStorageController@download');
    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
});

Route::namespace('Web')->middleware('web-middleware')->group(function () {
    Route::get('login/github', 'Auth\LoginController@redirectToGithub');
    Route::get('login/github/callback', 'Auth\LoginController@handleGithubCallback');
    Route::get('login/google', 'Auth\LoginController@redirectToGoogle');
    Route::get('login/google/callback', 'Auth\LoginController@handleGoogleCallback');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('/oauth/login', 'OAuthController@index');
    Route::get('/oauth/user', 'OAuthController@index');
    Route::post('/oauth/login', 'OAuthController@store');
    Route::get('/oauth/login/callback', 'OAuthController@handleCallback');
    Route::get('/oauth/login/google', 'OAuthController@redirectToGoogle');
    Route::get('/oauth/login/google/callback', 'OAuthController@handleGoogleCallback');
});

Route::namespace('Web')->middleware(['web-middleware', 'auth:web'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
});
