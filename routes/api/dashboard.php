<?php

Route::prefix('dashboard')->group(function () {
    Route::GET('chart-sales-value', 'DashboardController@ChartSalesValue');
    Route::GET('chart-sales-count', 'DashboardController@ChartSalesCount');
    Route::GET('chart-purchase-count', 'DashboardController@ChartPurchaseCount');
});
