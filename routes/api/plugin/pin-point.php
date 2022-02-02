<?php

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('pin-point')->namespace('PinPoint')->group(function () {
        Route::post('sales-visitation-forms/export', 'SalesVisitationExportController@export');
        Route::get('sales-visitation-forms', 'SalesVisitationController@index');
        Route::post('sales-visitation-forms', 'SalesVisitationController@store');
        Route::get('similar-products', 'SimilarProductController@index');
        Route::post('similar-products', 'SimilarProductController@store');
        Route::get('interest-reasons', 'InterestReasonController@index');
        Route::post('interest-reasons', 'InterestReasonController@store');
        Route::get('no-interest-reasons', 'NoInterestReasonController@index');
        Route::post('no-interest-reasons', 'NoInterestReasonController@store');

        Route::apiResource('sales-visitation-targets', 'SalesVisitationTargetController');
        Route::prefix('report')->namespace('Report')->group(function () {
            Route::post('performances/export', 'PerformanceReportExportController@export');
            Route::get('performances', 'PerformanceReportController@index');

            Route::get('accumulation', 'AccumulationReportController@index');
            
        });
    });
});
