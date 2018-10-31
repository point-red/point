<?php

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('pin-point')->namespace('PinPoint')->group(function () {
        Route::post('sales-visitation-forms', 'SalesVisitationController@store');
    });
});
