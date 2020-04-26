<?php

use Illuminate\Support\Facades\Route;

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('play-book')->namespace('PlayBook')->group(function () {
        Route::apiResource('glossaries', 'GlossaryController')->except('edit');
        Route::apiResource('glossaries/{glossary}/histories', 'GlossaryHistoryController')->only('index');
        
        Route::resource('procedures', 'ProcedureController')->except('edit');
        Route::apiResource('procedures/{procedure}/histories', 'ProcedureHistoryController')->only('index');

        Route::resource('instructions', 'Instruction\InstructionController')->except('edit');
        Route::apiResource('instructions/{instruction}/steps', 'Instruction\StepController')->except('edit');
        Route::apiResource('instructions/{instruction}/histories', 'Instruction\HistoryController')->only('index');
    });
});
