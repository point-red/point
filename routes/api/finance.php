<?php

Route::prefix('finance')->namespace('Finance')->group(function () {
    Route::apiResource('payment-orders', 'PaymentOrder\\PaymentOrderController');
    Route::apiResource('cashes', 'Cash\\CashController');
    Route::apiResource('banks', 'Bank\\BankController');
    // Route::apiResource('cash-advance', 'CashAdvanceController');
    // Route::apiResource('budgeting', 'BudgetingController');
});
