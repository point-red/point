<?php

Route::prefix('sales')->namespace('Sales')->group(function () {
    Route::apiResource('contracts', 'SalesContract\\SalesContractController');
    // Route::apiResource('quotation', 'SalesQuotationController');
    Route::apiResource('orders', 'SalesOrder\\SalesOrderController');
    Route::apiResource('down-payments', 'SalesDownPayment\\SalesDownPaymentController');
    Route::apiResource('delivery-orders', 'DeliveryOrder\\DeliveryOrderController');
    Route::apiResource('delivery-notes', 'DeliveryNote\\DeliveryNoteController');
    Route::get('invoices/last-price/{itemId}', 'SalesInvoice\\SalesInvoicePricingController@lastPrice');
    Route::apiResource('invoices', 'SalesInvoice\\SalesInvoiceController');
    // Route::apiResource('return', 'ReturnController');
});
