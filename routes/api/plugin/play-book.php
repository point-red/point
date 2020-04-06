<?php

use Illuminate\Support\Facades\Route;

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('play-book')->namespace('PlayBook')->group(function () {
        Route::apiResource('glossaries', 'GlossaryController');
        Route::apiResource('glossaries/{glossary}/histories', 'GlossaryHistoryController')->only('index');
    });
});
