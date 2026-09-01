<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('inventory-recapitulations', 'InventoryRecapitulationController@index');
    Route::get('inventory-warehouse-recapitulations/{itemId}', 'InventoryWarehouseRecapitulationController@index');
    Route::get('inventory-details/{itemId}', 'InventoryDetailController@index');
    Route::get('inventory-dna/{itemId}', 'InventoryDnaController@index');
    Route::get('inventory-dna/{itemId}/all', 'InventoryDnaController@allDna');
    Route::get('inventory-warehouse-currentstock', 'InventoryWarehouseCurrentStockController@index');
    Route::apiResource('audits', 'InventoryAudit\\InventoryAuditController');

    Route::namespace('InventoryUsage')->group(function () {
        Route::post('usages/{id}/histories', 'InventoryUsageHistoryController@store');

        Route::group(['middleware' => ['tenant.module-access:inventory usage']], function () {
            Route::post('usages/{id}/approve', 'InventoryUsageApprovalController@approve');
            Route::post('usages/{id}/reject', 'InventoryUsageApprovalController@reject');
            Route::post('usages/approval/send', 'InventoryUsageApprovalController@sendApproval');
            Route::get('usages/approval', 'InventoryUsageApprovalController@index');

            Route::post('usages/{id}/cancellation-approve', 'InventoryUsageCancellationApprovalController@approve');
            Route::post('usages/{id}/cancellation-reject', 'InventoryUsageCancellationApprovalController@reject');

            Route::get('usages/{id}/histories', 'InventoryUsageHistoryController@index');

            Route::get('usages/export', 'InventoryUsageController@export');
            Route::apiResource('usages', 'InventoryUsageController');
        });
    });

    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    Route::apiResource('transfer-items', 'TransferItem\\TransferItemController');
    Route::post('transfer-items/{id}/close', 'TransferItem\\TransferItemController@close');
    Route::post('transfer-items/export', 'TransferItem\\TransferItemController@export');
    Route::post('transfer-items/{id}/approve', 'TransferItem\\TransferItemApprovalController@approve');
    Route::post('transfer-items/{id}/reject', 'TransferItem\\TransferItemApprovalController@reject');
    Route::post('transfer-items/{id}/cancellation-approve', 'TransferItem\\TransferItemCancellationApprovalController@approve');
    Route::post('transfer-items/{id}/cancellation-reject', 'TransferItem\\TransferItemCancellationApprovalController@reject');
    Route::post('transfer-items/{id}/close-approve', 'TransferItem\\TransferItemCloseApprovalController@approve');
    Route::get('transfer-items/{id}/histories', 'TransferItem\\TransferItemHistoryController@index');
    Route::post('transfer-items/histories', 'TransferItem\\TransferItemHistoryController@store');
    Route::get('approval/transfer-items', 'TransferItem\\TransferItemApprovalController@index');
    Route::post('approval/transfer-items/send', 'TransferItem\\TransferItemApprovalController@sendApproval');
    Route::apiResource('receive-items', 'TransferItem\\ReceiveItemController');
    Route::post('receive-items/{id}/approve', 'TransferItem\\ReceiveItemApprovalController@approve');
    Route::post('receive-items/{id}/reject', 'TransferItem\\ReceiveItemApprovalController@reject');
    Route::post('receive-items/{id}/cancellation-approve', 'TransferItem\\ReceiveItemCancellationApprovalController@approve');
    Route::post('receive-items/{id}/cancellation-reject', 'TransferItem\\ReceiveItemCancellationApprovalController@reject');
    Route::post('receive-items/{id}/send', 'TransferItem\\ReceiveItemController@sendApproval');
    Route::post('receive-items/export', 'TransferItem\\ReceiveItemController@export');
    Route::get('receive-items/{id}/histories', 'TransferItem\\ReceiveItemHistoryController@index');
    Route::post('receive-items/histories', 'TransferItem\\ReceiveItemHistoryController@store');
});
