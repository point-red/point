<?php

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('pin-point')->namespace('PinPoint')->group(function () {
        Route::post('sales-visitation-forms/export', 'SalesVisitationExportController@export');
        Route::get('sales-visitation-forms', 'SalesVisitationController@index');
        Route::post('sales-visitation-forms', 'SalesVisitationController@store');

        Route::apiResource('sales-visitation-targets', 'SalesVisitationTargetController');
        Route::prefix('report')->namespace('Report')->group(function () {
            Route::post('performance/export', 'PerformanceReportExportController@export');
            Route::get('performance', 'PerformanceReportController@index');
        });
    });
});
