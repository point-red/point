<?php

Route::prefix('purchase')->namespace('Purchase')->group(function () {
    Route::get('pending', 'PurchasePendingController@index');
    Route::apiResource('contracts', 'PurchaseContract\\PurchaseContractController');
    Route::apiResource('requests', 'PurchaseRequest\\PurchaseRequestController');
    Route::apiResource('down-payments', 'PurchaseDownPayment\\PurchaseDownPaymentController');
    Route::apiResource('orders', 'PurchaseOrder\\PurchaseOrderController');
    Route::apiResource('receives', 'PurchaseReceive\\PurchaseReceiveController');
    Route::apiResource('invoices', 'PurchaseInvoice\\PurchaseInvoiceController');
    // Route::apiResource('return', 'ReturnController');
});
