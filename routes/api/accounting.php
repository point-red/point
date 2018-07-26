<?php

Route::prefix('accounting')->namespace('Accounting')->group(function () {
    Route::apiResource('chart-of-account-groups', 'ChartOfAccountGroupController');
    Route::apiResource('chart-of-account-types', 'ChartOfAccountTypeController');
    Route::apiResource('chart-of-accounts', 'ChartOfAccountController');
    Route::apiResource('cut-offs', 'CutOffController');
    Route::apiResource('balance-sheets', 'BalanceSheetController');
});
