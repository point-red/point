<?php

use App\Model\Sales\DeliveryOrder\DeliveryOrder;

Route::prefix('sales')->namespace('Sales')->group(function () {
    Route::apiResource('contracts', 'SalesContract\\SalesContractController');
    Route::post('quotations/{id}/approve', 'SalesQuotation\\SalesQuotationApprovalController@approve');
    Route::post('quotations/{id}/reject', 'SalesQuotation\\SalesQuotationApprovalController@reject');
    Route::post('quotations/{id}/cancellation-approve', 'SalesQuotation\\SalesQuotationCancellationApprovalController@approve');
    Route::post('quotations/{id}/cancellation-reject', 'SalesQuotation\\SalesQuotationCancellationApprovalController@reject');
    Route::apiResource('quotations', 'SalesQuotation\\SalesQuotationController');
    Route::post('orders/{id}/approve', 'SalesOrder\\SalesOrderApprovalController@approve');
    Route::post('orders/{id}/reject', 'SalesOrder\\SalesOrderApprovalController@reject');
    Route::post('orders/{id}/cancellation-approve', 'SalesOrder\\SalesOrderCancellationApprovalController@approve');
    Route::post('orders/{id}/cancellation-reject', 'SalesOrder\\SalesOrderCancellationApprovalController@reject');
    Route::apiResource('orders', 'SalesOrder\\SalesOrderController');
    Route::post('down-payments/{id}/approve', 'SalesDownPayment\\SalesDownPaymentApprovalController@approve');
    Route::post('down-payments/{id}/reject', 'SalesDownPayment\\SalesDownPaymentApprovalController@reject');
    Route::post('down-payments/{id}/cancellation-approve', 'SalesDownPayment\\SalesDownPaymentCancellationApprovalController@approve');
    Route::post('down-payments/{id}/cancellation-reject', 'SalesDownPayment\\SalesDownPaymentCancellationApprovalController@reject');
    Route::apiResource('down-payments', 'SalesDownPayment\\SalesDownPaymentController');

    Route::namespace('DeliveryOrder')
        ->middleware('tenant.module-access:sales delivery order')
        ->group(function () {
            Route::post('delivery-orders/{id}/approve', 'DeliveryOrderApprovalController@approve');
            Route::post('delivery-orders/{id}/reject', 'DeliveryOrderApprovalController@reject');
            Route::post('delivery-orders/approval/send', 'DeliveryOrderApprovalController@sendApproval');
            Route::get('delivery-orders/approval', 'DeliveryOrderApprovalController@index');

            Route::post('delivery-orders/{id}/cancellation-approve', 'DeliveryOrderCancellationApprovalController@approve');
            Route::post('delivery-orders/{id}/cancellation-reject', 'DeliveryOrderCancellationApprovalController@reject');

            Route::post('delivery-orders/{id}/close-approve', 'DeliveryOrderCloseApprovalController@approve');
            Route::post('delivery-orders/{id}/close-reject', 'DeliveryOrderCloseApprovalController@reject');
            Route::post('delivery-orders/{id}/close', 'DeliveryOrderCloseApprovalController@close');

            Route::get('delivery-orders/{id}/histories', 'DeliveryOrderHistoryController@index');

            Route::get('delivery-orders/{id}/receipt', 'DeliveryOrderController@showReceipt');
            Route::get('delivery-orders/export', 'DeliveryOrderController@export');
            Route::apiResource('delivery-orders', 'DeliveryOrderController');
        });

    Route::apiResource('delivery-notes', 'DeliveryNote\\DeliveryNoteController');
    Route::get('invoices/last-price/{itemId}', 'SalesInvoice\\SalesInvoicePricingController@lastPrice');
    Route::apiResource('invoices', 'SalesInvoice\\SalesInvoiceController');
    Route::apiResource('return', 'SalesReturn\\SalesReturnController');
});
