<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::get('pending', 'PurchasePendingController@index');
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
    Route::apiResource('down-payments', 'PurchaseDownPayment\\PurchaseDownPaymentController');
    Route::resource('receives', 'PurchaseReceive\\PurchaseReceiveController');
    Route::apiResource('invoices', 'PurchaseInvoice\\PurchaseInvoiceController');
    Route::apiResource('return', 'PurchaseReturn\\PurchaseReturnController');
});
