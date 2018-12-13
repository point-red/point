<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::apiResource('purchase-request', 'PurchaseRequest\\PurchaseRequestController');
    // Route::apiResource('purchase-order', 'PurchaseOrderController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    // Route::apiResource('received-order', 'ReceivedOrderController');
    // Route::apiResource('invoice', 'InvoiceController');
    // Route::apiResource('return', 'ReturnController');
    // Route::apiResource('payment-order', 'PaymentOrderController');
});
