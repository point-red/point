<?php

Route::prefix('finance')->namespace('Finance')->group(function () {
    Route::get('payments/get-references', 'Payment\\PaymentController@getReferences');
    Route::post('payments/{id}/cancellation-approve', 'Payment\\PaymentCancellationApprovalController@approve');
    Route::post('payments/{id}/cancellation-reject', 'Payment\\PaymentCancellationApprovalController@reject');
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
    Route::post('cash-advances/send-bulk-request-approval', 'CashAdvance\\CashAdvanceController@sendBulkRequestApproval');
    Route::post('cash-advances/{id}/refund', 'CashAdvance\\CashAdvanceController@refund');
    Route::get('cash-advances/history', 'CashAdvance\\CashAdvanceController@history');
    Route::post('cash-advances/history', 'CashAdvance\\CashAdvanceController@storeHistory');
    Route::apiResource('cash-advances', 'CashAdvance\\CashAdvanceController');
    Route::apiResource('reports', 'Report\\ReportController');
    Route::post('reports/set-checklist', 'Report\\ReportController@setChecklist');
    // Route::apiResource('budgeting', 'BudgetingController');
});
