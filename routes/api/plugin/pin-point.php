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

            Route::prefix('accumulation')->namespace('Accumulation')->group(function () {
                Route::post('interest-reasons/export', 'InterestReasonReportController@export');
                Route::get('interest-reasons', 'InterestReasonReportController@index');

                Route::post('no-interest-reasons/export', 'NoInterestReasonReportController@export');
                Route::get('no-interest-reasons', 'NoInterestReasonReportController@index');

                Route::post('similar-products/export', 'SimilarProductReportController@export');
                Route::get('similar-products', 'SimilarProductReportController@index');

                Route::post('repeat-orders/export', 'RepeatOrderReportController@export');
                Route::get('repeat-orders', 'RepeatOrderReportController@index');
            });
        });
    });
});
