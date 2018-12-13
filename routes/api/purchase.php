<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::apiResource('purchase-request', 'PurchaseRequest\\PurchaseRequestController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    Route::apiResource('purchase-order', 'PurchaseOrder\\PurchaseOrderController');
    // Route::apiResource('received-order', 'ReceivedOrderController');
    // Route::apiResource('invoice', 'InvoiceController');
    // Route::apiResource('return', 'ReturnController');
    // Route::apiResource('payment-order', 'PaymentOrderController');
});
