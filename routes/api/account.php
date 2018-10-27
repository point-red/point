<?php

Route::prefix('account')->namespace('Account')->group(function () {
    Route::apiResource('profiles', 'ProfileController');
    Route::patch('password/{user_id}', 'PasswordController@update');

    Route::get('account-payable', 'AccountPayableController@index');
    Route::get('account-receivable', 'AccountReceivableController@index');
});
