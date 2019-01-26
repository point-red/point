<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->namespace('Api')->group(function () {
    Route::post('register', 'RegisterController@store');

    Route::prefix('auth')->namespace('Auth')->group(function () {
        Route::post('login', 'LoginController@index');
        Route::post('fetch', 'FetchController@index');
        Route::get('reset-password-request', 'ResetPasswordController@index');
        Route::get('reset-password', 'ResetPasswordController@store');
    });

    // This routes below require authentication
    Route::middleware('auth:api')->group(function () {
        Route::post('auth-user', 'AuthUserController@show');
        require base_path('routes/api/account.php');
        require base_path('routes/api/project.php');

        // Global Transaction
        Route::resource('transactions', 'TransactionController');

        // Tenant
        require base_path('routes/api/master.php');
        require base_path('routes/api/purchase.php');
        require base_path('routes/api/sales.php');
        require base_path('routes/api/finance.php');
        require base_path('routes/api/accounting.php');
        require base_path('routes/api/human-resource.php');

        // Plugin
        require base_path('routes/api/plugin/scale-weight.php');
        require base_path('routes/api/plugin/pin-point.php');
    });
});
