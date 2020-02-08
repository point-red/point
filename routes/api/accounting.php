<?php

Route::prefix('accounting')->namespace('Accounting')->group(function () {
    Route::apiResource('chart-of-account-groups', 'ChartOfAccountGroupController');
    Route::get('chart-of-account-types', 'ChartOfAccountTypeController@index');
    Route::get('chart-of-account-sub-ledgers', 'ChartOfAccountSubLedgerController@index');
    Route::apiResource('chart-of-accounts', 'ChartOfAccountController');
    Route::apiResource('cut-offs', 'CutOffController');
    Route::apiResource('balance-sheets', 'BalanceSheetController');
    Route::apiResource('journals', 'JournalController');
    // Route::apiResource('memo-journals', 'MemoJournalController');
    Route::prefix('ratio-report')->namespace('RatioReport')->group(function () {
        // Liquidity Ratio
        Route::get('current-ratios', 'CurrentRatioController@index');
        Route::get('cash-ratios', 'CashRatioController@index');
        Route::get('acid-test-ratios', 'AcidTestRatioController@index');
        // Profitability Ratio
        Route::get('gross-profit-ratios', 'GrossProfitRatioController@index');
        Route::get('net-profit-margins', 'NetProfitMarginController@index');
        Route::get('rate-of-return-investment', 'RateOfReturnInvestmentController@index');
        Route::get('return-on-equities', 'ReturnOnEquityController@index');
        Route::get('rate-of-return-on-net-worth', 'RateOfReturnOnNetWorthController@index');
        // Leverage Ratio
        Route::get('total-debt-to-asset-ratios', 'TotalDebtToAssetRatioController@index');
        Route::get('total-debt-to-equity-ratios', 'TotalDebtToEquityRatioController@index');
        // Activity Ratio
        Route::get('total-asset-turn-overs', 'TotalAssetTurnOverController@index');
        Route::get('working-capital-turn-overs', 'WorkingCapitalTurnOverController@index');
        Route::get('fixed-asset-turn-overs', 'FixedAssetTurnOverController@index');
        Route::get('inventory-turn-overs', 'InventoryTurnOverController@index');
        Route::get('average-collection-period-ratios', 'AverageCollectionPeriodRatioController@index');
    });

    Route::get('account-payable', 'AccountPayableController@index');
    Route::get('account-receivable', 'AccountReceivableController@index');
});
