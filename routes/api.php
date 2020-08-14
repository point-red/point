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

Route::prefix('v1')->namespace('Api')->middleware('api-middleware')->group(function () {
    Route::post('register', 'RegisterController@store');

    Route::prefix('payment-gateway/xendit-callback')->namespace('PaymentGateway\\Xendit')->group(function () {
        Route::post('/invoice-paid', 'XenditCallbackController@invoicePaid');
        Route::post('/fva-created', 'XenditCallbackController@fvaCreated');
        Route::post('/fva-paid', 'XenditCallbackController@fvaPaid');
        Route::post('/retail-outlet-paid', 'XenditCallbackController@retailOutletPaid');
        Route::post('/card-refunded', 'XenditCallbackController@cardRefunded');
        Route::post('/disbursement-sent', 'XenditCallbackController@disbursementSent');
        Route::post('/batch-disbursement-sent', 'XenditCallbackController@batchDisbursementSent');
    });

    Route::prefix('auth')->namespace('Auth')->group(function () {
        Route::post('login', 'LoginController@index');
        Route::post('logout', 'LogoutController@index');
        Route::post('fetch', 'FetchController@index');
        Route::get('reset-password-request', 'ResetPasswordController@index');
        Route::get('reset-password', 'ResetPasswordController@store');
    });

    // This routes below require authentication
    Route::middleware('auth:api')->group(function () {
        Route::post('send-email', 'EmailServiceController@send');
        Route::post('auth-user', 'AuthUserController@show');
        require base_path('routes/api/account.php');
        require base_path('routes/api/project.php');
        Route::apiResource('invoices', 'InvoiceController');

        // Global Transaction
        Route::resource('transactions', 'TransactionController');
        Route::apiResource('firebase-token', 'FirebaseTokenController');
        Route::post('storage/upload', 'StorageController@upload');
        Route::apiResource('storage', 'StorageController');
        require base_path('routes/api/reward.php');

        //
        Route::prefix('account')->namespace('Account')->group(function () {
            Route::get('wallets', 'WalletController@index');
            Route::get('wallets/amount', 'WalletController@amount');
            Route::post('wallets/top-up', 'WalletController@topUp');
            Route::post('wallets/pay', 'WalletController@pay');
        });

        // Tenant
        require base_path('routes/api/master.php');
        require base_path('routes/api/purchase.php');
        require base_path('routes/api/sales.php');
        require base_path('routes/api/manufacture.php');
        require base_path('routes/api/pos.php');
        require base_path('routes/api/finance.php');
        require base_path('routes/api/accounting.php');
        require base_path('routes/api/human-resource.php');
        require base_path('routes/api/inventory.php');
        require base_path('routes/api/dashboard.php');
        require base_path('routes/api/reward.php');

        // Plugin
        require base_path('routes/api/plugin/scale-weight.php');
        require base_path('routes/api/plugin/pin-point.php');
        require base_path('routes/api/plugin/play-book.php');
    });
});
