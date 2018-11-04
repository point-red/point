<?php

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('scale-weight')->namespace('ScaleWeight')->group(function () {
        Route::post('trucks/export', 'ScaleWeightTruckExportController@export');
        Route::apiResource('trucks', 'ScaleWeightTruckController');
        Route::apiResource('items', 'ScaleWeightItemController');
    });
});
