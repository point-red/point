<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::post('contracts/{id}/approve', 'PurchaseContract\\PurchaseContractApprovalController@approve');
    Route::post('contracts/{id}/reject', 'PurchaseContract\\PurchaseContractApprovalController@reject');
    Route::post('contracts/{id}/cancellation-approve', 'PurchaseContract\\PurchaseContractCancellationApprovalController@approve');
    Route::post('contracts/{id}/cancellation-reject', 'PurchaseContract\\PurchaseContractCancellationApprovalController@reject');
    Route::apiResource('contracts', 'PurchaseContract\\PurchaseContractController');
    Route::post('requests/{id}/approve', 'PurchaseRequest\\PurchaseRequestApprovalController@approve');
    Route::post('requests/{id}/reject', 'PurchaseRequest\\PurchaseRequestApprovalController@reject');
    Route::post('requests/{id}/cancellation-approve', 'PurchaseRequest\\PurchaseRequestCancellationApprovalController@approve');
    Route::post('requests/{id}/cancellation-reject', 'PurchaseRequest\\PurchaseRequestCancellationApprovalController@reject');
    Route::apiResource('requests', 'PurchaseRequest\\PurchaseRequestController');
    Route::post('orders/{id}/approve', 'PurchaseOrder\\PurchaseOrderApprovalController@approve');
    Route::post('orders/{id}/reject', 'PurchaseOrder\\PurchaseOrderApprovalController@reject');
    Route::post('orders/{id}/cancellation-approve', 'PurchaseOrder\\PurchaseOrderCancellationApprovalController@approve');
    Route::post('orders/{id}/cancellation-reject', 'PurchaseOrder\\PurchaseOrderCancellationApprovalController@reject');
    Route::apiResource('orders', 'PurchaseOrder\\PurchaseOrderController');
    Route::post('down-payments/{id}/approve', 'PurchaseDownPayment\\PurchaseDownPaymentApprovalController@approve');
    Route::post('down-payments/{id}/reject', 'PurchaseDownPayment\\PurchaseDownPaymentApprovalController@reject');
    Route::post('down-payments/{id}/cancellation-approve', 'PurchaseDownPayment\\PurchaseDownPaymentCancellationApprovalController@approve');
    Route::post('down-payments/{id}/cancellation-reject', 'PurchaseDownPayment\\PurchaseDownPaymentCancellationApprovalController@reject');
    Route::apiResource('down-payments', 'PurchaseDownPayment\\PurchaseDownPaymentController');
    Route::post('receives/{id}/approve', 'PurchaseReceive\\PurchaseReceiveApprovalController@approve');
    Route::post('receives/{id}/reject', 'PurchaseReceive\\PurchaseReceiveApprovalController@reject');
    Route::post('receives/{id}/cancellation-approve', 'PurchaseReceive\\PurchaseReceiveCancellationApprovalController@approve');
    Route::post('receives/{id}/cancellation-reject', 'PurchaseReceive\\PurchaseReceiveCancellationApprovalController@reject');
    Route::resource('receives', 'PurchaseReceive\\PurchaseReceiveController');
    Route::post('invoices/{id}/approve', 'PurchaseInvoice\\PurchaseInvoiceApprovalController@approve');
    Route::post('invoices/{id}/reject', 'PurchaseInvoice\\PurchaseInvoiceApprovalController@reject');
    Route::post('invoices/{id}/cancellation-approve', 'PurchaseInvoice\\PurchaseInvoiceCancellationApprovalController@approve');
    Route::post('invoices/{id}/cancellation-reject', 'PurchaseInvoice\\PurchaseInvoiceCancellationApprovalController@reject');
    Route::apiResource('invoices', 'PurchaseInvoice\\PurchaseInvoiceController');
    Route::post('return/{id}/approve', 'PurchaseReturn\\PurchaseReturnApprovalController@approve');
    Route::post('return/{id}/reject', 'PurchaseReturn\\PurchaseReturnApprovalController@reject');
    Route::post('return/{id}/cancellation-approve', 'PurchaseReturn\\PurchaseReturnCancellationApprovalController@approve');
    Route::post('return/{id}/cancellation-reject', 'PurchaseReturn\\PurchaseReturnCancellationApprovalController@reject');
    Route::apiResource('return', 'PurchaseReturn\\PurchaseReturnController');
});
