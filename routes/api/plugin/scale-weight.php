<?php

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('scale-weight')->namespace('ScaleWeight')->group(function () {
        Route::post('trucks/export', 'ScaleWeightTruckExportController@export');
        Route::apiResource('trucks', 'ScaleWeightTruckController');
        Route::post('items/export', 'ScaleWeightItemExportController@export');
        Route::apiResource('items', 'ScaleWeightItemController');
        Route::post('merge/export', 'ScaleWeightMergeExportController@export');
        Route::get('merge/item', 'ScaleWeightMergeController@item');
        Route::apiResource('merge', 'ScaleWeightMergeController');
    });
});
