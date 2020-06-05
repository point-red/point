<?php

Route::prefix('finance')->namespace('Finance')->group(function () {
    Route::apiResource('payments', 'Payment\\PaymentController');
    Route::post('payment-orders/{id}/approve', 'Payment\\PaymentOrderApprovalController@approve');
    Route::post('payment-orders/{id}/reject', 'Payment\\PaymentOrderApprovalController@reject');
    Route::post('payment-orders/{id}/cancellation-approve', 'Payment\\PaymentOrderCancellationApprovalController@approve');
    Route::post('payment-orders/{id}/cancellation-reject', 'Payment\\PaymentOrderCancellationApprovalController@reject');
    Route::apiResource('payment-orders', 'Payment\\PaymentOrderController');
    // Route::apiResource('cash-advance', 'CashAdvanceController');
    // Route::apiResource('budgeting', 'BudgetingController');
});
