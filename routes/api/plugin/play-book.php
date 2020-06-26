<?php

use Illuminate\Support\Facades\Route;

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('play-book')->namespace('PlayBook')->group(function () {
        Route::resource('glossaries', 'GlossaryController')->except('edit');
        Route::apiResource('glossaries/{glossary}/histories', 'GlossaryHistoryController')->only('index');

        Route::resource('procedures', 'ProcedureController')->except('edit');
        Route::apiResource('procedures/{procedure}/histories', 'ProcedureHistoryController')->only('index');

        Route::resource('instructions', 'Instruction\InstructionController')->except('edit');
        Route::apiResource('instructions/{instruction}/steps', 'Instruction\StepController')->except('edit');
        Route::apiResource('instructions/{instruction}/histories', 'Instruction\HistoryController')->only('index');

        Route::get('approval/procedures', 'Approval\ProcedureController@index');
        Route::post('approval/procedures/send', 'Approval\ProcedureController@sendApproval');
        Route::post('approval/procedures/{procedure}/approve', 'Approval\ProcedureController@approve');
        Route::post('approval/procedures/{procedure}/decline', 'Approval\ProcedureController@decline');
        Route::get('approval/instructions', 'Approval\InstructionController@index');
        Route::post('approval/instructions/send', 'Approval\InstructionController@sendApproval');
        Route::post('approval/instructions/{instruction}/approve', 'Approval\InstructionController@approve');
        Route::post('approval/instructions/{instruction}/decline', 'Approval\InstructionController@decline');
        Route::post('approval/instructions/{instruction}/approve/step/{step}', 'Approval\InstructionController@approveStep');
        Route::post('approval/instructions/{instruction}/decline/step/{step}', 'Approval\InstructionController@declineStep');
    });
});
