<?php

Route::prefix('plugin')->namespace('plugin')->group(function () {
    Route::prefix('scale-weight')->namespace('ScaleWeight')->group(function () {
        Route::apiResource('trucks', 'ScaleWeightTruckController');
        Route::apiResource('items', 'ScaleWeightItemController');
    });
});
