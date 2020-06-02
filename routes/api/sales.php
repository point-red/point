<?php

Route::prefix('sales')->namespace('Sales')->group(function () {
    Route::apiResource('contracts', 'SalesContract\\SalesContractController');
    Route::post('quotations/{id}/approve', 'SalesQuotation\\SalesQuotationApprovalController@approve');
    Route::post('quotations/{id}/reject', 'SalesQuotation\\SalesQuotationApprovalController@reject');
    Route::post('quotations/{id}/cancellation-approve', 'SalesQuotation\\SalesQuotationCancellationApprovalController@approve');
    Route::post('quotations/{id}/cancellation-reject', 'SalesQuotation\\SalesQuotationCancellationApprovalController@reject');
    Route::apiResource('quotations', 'SalesQuotation\\SalesQuotationController');
    Route::apiResource('orders', 'SalesOrder\\SalesOrderController');
    Route::apiResource('down-payments', 'SalesDownPayment\\SalesDownPaymentController');
    Route::apiResource('delivery-orders', 'DeliveryOrder\\DeliveryOrderController');
    Route::apiResource('delivery-notes', 'DeliveryNote\\DeliveryNoteController');
    Route::get('invoices/last-price/{itemId}', 'SalesInvoice\\SalesInvoicePricingController@lastPrice');
    Route::apiResource('invoices', 'SalesInvoice\\SalesInvoiceController');
    Route::apiResource('return', 'SalesReturn\\SalesReturnController');
});
