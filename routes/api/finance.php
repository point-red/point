<?php

Route::prefix('finance')->namespace('Finance')->group(function () {
    Route::apiResource('payments', 'Payment\\PaymentController');
    Route::apiResource('payment-orders', 'Payment\\PaymentOrderController');
    // Route::apiResource('cash-advance', 'CashAdvanceController');
    // Route::apiResource('budgeting', 'BudgetingController');
});
