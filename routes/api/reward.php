<?php

Route::prefix('reward')->namespace('Reward')->group(function () {
    Route::resource('tokens', 'TokenController')->only(['index', 'show']);

    Route::get('token-generators', 'TokenGeneratorController@index');
    Route::get('token-generators/{id}', 'TokenGeneratorController@show');
    Route::put('token-generators', 'TokenGeneratorController@update');
});
