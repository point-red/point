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

Route::view('/', 'welcome');

Route::namespace('Web')->group(function () {
    Route::get('/download', 'CloudStorageController@download');
    Route::get('/phpinfo', function () {
        phpinfo();
    });
});
