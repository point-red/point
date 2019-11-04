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

    Route::get('test', function () {
        return response()->json(['message' => 'welcome']);
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

        // Global Transaction
        Route::resource('transactions', 'TransactionController');
        Route::apiResource('firebase-token', 'FirebaseTokenController');
        Route::apiResource('storage', 'StorageController');
        require base_path('routes/api/reward.php');

        // Tenant
        require base_path('routes/api/master.php');
        require base_path('routes/api/purchase.php');
        require base_path('routes/api/sales.php');
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
    });

    Route::prefix('psychotest')->namespace('Psychotest')->group(function () {
        Route::post('candidates/login', 'CandidateController@login');
        Route::apiResource('candidates', 'CandidateController');
        Route::apiResource('candidate-positions', 'CandidatePositionController');
        Route::apiResource('position-categories', 'PositionCategoryController');
        
        Route::apiResource('kraepelins', 'KraepelinController');
        Route::apiResource('kraepelin-columns', 'KraepelinColumnController');

        Route::apiResource('papikosticks', 'PapikostickController');
        Route::apiResource('papikostick-categories', 'PapikostickCategoryController');
        Route::apiResource('papikostick-questions', 'PapikostickQuestionController');
        Route::apiResource('papikostick-options', 'PapikostickOptionController');
        Route::apiResource('papikostick-results', 'PapikostickResultController');
    });

    // These routes below using client_credentials tokens for the authentication
    Route::middleware('client')->group(function () {
        require base_path('routes/api/reward.php');
    });
});
