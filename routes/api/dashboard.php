<?php

Route::prefix('dashboard')->group(function () {
    Route::GET('chart-sales-value', 'DashboardController@chartSalesValue');
    Route::GET('chart-sales-count', 'DashboardController@chartSalesCount');
    Route::GET('chart-purchase-value', 'DashboardController@chartPurchaseValue');
    Route::GET('chart-purchase-count', 'DashboardController@chartPurchaseCount');
    Route::get('chart-payment-received', 'DashboardController@chartPaymentReceived');
    Route::get('chart-payment-sent', 'DashboardController@chartPaymentSent');
});
