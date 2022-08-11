<?php

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

    Route::namespace('DeliveryOrder')->group(function () {
        Route::post('delivery-orders/{id}/histories', 'DeliveryOrderHistoryController@store');

        Route::group(['middleware' => ['tenant.module-access:sales delivery order']], function () {
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

            Route::get('delivery-orders/export', 'DeliveryOrderController@export');
            Route::apiResource('delivery-orders', 'DeliveryOrderController');
        });
    });

    Route::namespace('DeliveryNote')->group(function () {
        Route::group(['middleware' => ['tenant.module-access:sales delivery note']], function () {
            Route::get('delivery-notes/export', 'DeliveryNoteController@export');
            Route::apiResource('delivery-notes', 'DeliveryNoteController');
        });
    });

    Route::get('invoices/last-price/{itemId}', 'SalesInvoice\\SalesInvoicePricingController@lastPrice');
    Route::apiResource('invoices', 'SalesInvoice\\SalesInvoiceController');
    Route::apiResource('return', 'SalesReturn\\SalesReturnController');

    Route::apiResource('payment-collection', 'PaymentCollection\\PaymentCollectionController');
    Route::get('payment-collection/{customerId}/references', 'PaymentCollection\\PaymentCollectionReferenceController@customerSalesForms');
    Route::post('payment-collection/{id}/approve', 'PaymentCollection\\PaymentCollectionApprovalController@approve');
    Route::post('payment-collection/{id}/reject', 'PaymentCollection\\PaymentCollectionApprovalController@reject');
    Route::post('payment-collection/histories', 'PaymentCollection\\PaymentCollectionHistoryController@store');
    Route::get('payment-collection/{id}/histories', 'PaymentCollection\\PaymentCollectionHistoryController@index');
    Route::get('approval/payment-collection', 'PaymentCollection\\PaymentCollectionApprovalController@index');
    Route::post('approval/payment-collection', 'PaymentCollection\\PaymentCollectionApprovalController@sendApproval');
    Route::post('approval/payment-collection/send', 'PaymentCollection\\PaymentCollectionApprovalController@sendApproval');
    Route::post('approval/payment-collection/{id}/send', 'PaymentCollection\\PaymentCollectionApprovalController@sendApprovalSingle');
    Route::post('approval/payment-collection/cancellation/{id}/send', 'PaymentCollection\\PaymentCollectionApprovalController@sendCancellationApprovalSingle');
    Route::post('payment-collection/{id}/cancellation-approve', 'PaymentCollection\\PaymentCollectionCancellationApprovalController@approve');
    Route::post('payment-collection/{id}/cancellation-reject', 'PaymentCollection\\PaymentCollectionCancellationApprovalController@reject');
    Route::post('payment-collection/export', 'PaymentCollection\\PaymentCollectionController@export');
    Route::post('payment-collection/generate-number', 'PaymentCollection\\PaymentCollectionController@generateFormNumber');
});
