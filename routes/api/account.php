<?php

Route::prefix('account')->namespace('Account')->group(function () {
    Route::apiResource('profiles', 'ProfileController');
});
