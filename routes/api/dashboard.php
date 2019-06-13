<?php

Route::prefix('dashboard')->group(function () {
    Route::GET('chart-sales-value', 'DashboardController@ChartSalesValue');
    Route::GET('chart-sales-count', 'DashboardController@ChartSalesCount');
    Route::GET('chart-purchase-value', 'DashboardController@ChartPurchaseValue');
    Route::GET('chart-purchase-count', 'DashboardController@ChartPurchaseCount');
    Route::GET('stat-total-receivable', 'DashboardController@statTotalReceivable');
    Route::GET('stat-total-payable', 'DashboardController@statTotalPayable');
});
