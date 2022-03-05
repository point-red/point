<?php

Route::prefix('finance')->namespace('Finance')->group(function () {
    Route::apiResource('payments', 'Payment\\PaymentController');
    Route::post('payment-orders/{id}/approve', 'Payment\\PaymentOrderApprovalController@approve');
    Route::post('payment-orders/{id}/reject', 'Payment\\PaymentOrderApprovalController@reject');
    Route::post('payment-orders/{id}/cancellation-approve', 'Payment\\PaymentOrderCancellationApprovalController@approve');
    Route::post('payment-orders/{id}/cancellation-reject', 'Payment\\PaymentOrderCancellationApprovalController@reject');
    Route::apiResource('payment-orders', 'Payment\\PaymentOrderController');
    Route::post('cash-advances/{id}/approve', 'CashAdvance\\CashAdvanceApprovalController@approve');
    Route::post('cash-advances/{id}/reject', 'CashAdvance\\CashAdvanceApprovalController@reject');
    Route::post('cash-advances/{id}/cancellation-approve', 'CashAdvance\\CashAdvanceCancellationApprovalController@approve');
    Route::post('cash-advances/{id}/cancellation-reject', 'CashAdvance\\CashAdvanceCancellationApprovalController@reject');
    Route::get('cash-advances/history', 'CashAdvance\\CashAdvanceController@history');
    Route::apiResource('cash-advances', 'CashAdvance\\CashAdvanceController');
    // Route::apiResource('budgeting', 'BudgetingController');
});
