<?php

Route::prefix('sales')->namespace('Sales')->group(function () {
    // Route::apiResource('sales-quotation', 'SalesQuotationController');
    Route::apiResource('sales-orders', 'SalesOrder\\SalesOrderController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    // Route::apiResource('delivery-order', 'DeliveryOrderController');
    // Route::apiResource('invoice', 'InvoiceController');
    // Route::apiResource('return', 'ReturnController');
    // Route::apiResource('payment-collection', 'PaymentCollectionController');
});
