<?php

Route::prefix('project')->namespace('Project')->group(function () {
    Route::delete('project-users', 'ProjectUserController@destroy');
    Route::apiResource('projects', 'ProjectController');
    Route::get('projects/{id}/database/backups', 'DatabaseBackupController@index');
    Route::post('projects/{id}/database/backups', 'DatabaseBackupController@store');
    Route::post('projects/{id}/database/reset', 'ResetDatabaseController@index');
    Route::get('projects/{id}/database/tables', 'DatabaseController@index');
    Route::get('projects/{id}/database/tables/{table_name}', 'DatabaseController@show');
    Route::get('projects/{id}/preferences', 'ProjectPreferenceController@show');
    Route::put('projects/{id}/preferences', 'ProjectPreferenceController@update');
    Route::patch('projects/{id}/preferences', 'ProjectPreferenceController@update');
    Route::apiResource('request-join', 'RequestJoinController');
});

Route::apiResource('plugins', 'PluginController');
