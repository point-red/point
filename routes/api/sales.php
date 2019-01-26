<?php

Route::prefix('sales')->namespace('Sales')->group(function () {
    // Route::apiResource('sales-quotation', 'SalesQuotationController');
    Route::apiResource('sales-orders', 'SalesOrder\\SalesOrderController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    Route::apiResource('delivery-orders', 'DeliveryOrder\\DeliveryOrderController');
    Route::apiResource('delivery-notes', 'DeliveryNote\\DeliveryNoteController');
    Route::apiResource('sales-invoices', 'SalesInvoice\\SalesInvoiceController');
    // Route::apiResource('sales-return', 'ReturnController');
});
