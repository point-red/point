<?php

Route::prefix('project')->namespace('Project')->group(function () {
    Route::apiResource('projects', 'ProjectController');
    Route::get('projects/{id}/preferences', 'ProjectPreferenceController@show');
    Route::put('projects/{id}/preferences', 'ProjectPreferenceController@update');
    Route::patch('projects/{id}/preferences', 'ProjectPreferenceController@update');
    Route::apiResource('request-join', 'RequestJoinController');
});
