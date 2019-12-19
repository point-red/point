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
    Route::get('/media/{id}', 'CloudStorageController@downloadMedia');
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
});

Route::namespace('Web')->middleware('web-middleware')->group(function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');
});

Route::namespace('Web')->middleware(['web-middleware', 'auth:web'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
});
