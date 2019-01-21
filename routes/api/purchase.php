<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::apiResource('purchase-requests', 'PurchaseRequest\\PurchaseRequestController');
    // Route::apiResource('downpayment', 'DownpaymentController');
    Route::apiResource('purchase-orders', 'PurchaseOrder\\PurchaseOrderController');
    Route::apiResource('purchase-receives', 'PurchaseReceive\\PurchaseReceiveController');
    Route::apiResource('purchase-invoices', 'PurchaseInvoice\\PurchaseInvoiceController');
    // Route::apiResource('purchase-return', 'ReturnController');
    Route::apiResource('payment-order', 'PurchasePaymentOrder\\PaymentOrderController');
});
