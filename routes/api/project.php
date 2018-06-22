<?php

Route::prefix('project')->namespace('Project')->group(function () {
    Route::apiResource('projects', 'ProjectController');
});
