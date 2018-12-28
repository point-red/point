<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::apiResource('purchase-requests', 'PurchaseRequest\\PurchaseRequestController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    Route::apiResource('purchase-orders', 'PurchaseOrder\\PurchaseOrderController');
    Route::apiResource('purchase-receives', 'PurchaseReceive\\PurchaseReceiveController');
    // Route::apiResource('invoice', 'InvoiceController');
    // Route::apiResource('return', 'ReturnController');
    // Route::apiResource('payment-order', 'PaymentOrderController');
});
