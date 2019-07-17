<?php

Route::prefix('setting')->namespace('Setting')->as('setting.')->group(function() {
    Route::get('reward/point', 'Reward\PointController@index');
    Route::put('reward/point', 'Reward\PointController@update');
});