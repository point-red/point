<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('inventory-recapitulations', 'InventoryRecapitulationController@index');
    Route::get('inventory-warehouse-recapitulations/{itemId}', 'InventoryWarehouseRecapitulationController@index');
    Route::get('inventory-details/{itemId}', 'InventoryDetailController@index');
    Route::get('inventory-dna/{itemId}', 'InventoryDnaController@index');
    Route::get('inventory-dna/{itemId}/all', 'InventoryDnaController@allDna');
    Route::get('inventory-warehouse-currentstock', 'InventoryWarehouseCurrentStockController@index');
    Route::apiResource('audits', 'InventoryAudit\\InventoryAuditController');
    Route::apiResource('usages', 'InventoryUsage\\InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    Route::get('approval/transfer-items', 'TransferItem\\TransferItemApprovalController@index');
    Route::post('approval/transfer-items/send', 'TransferItem\\TransferItemApprovalController@sendApproval');
    Route::post('transfer-items/{id}/approve', 'TransferItem\\TransferItemApprovalController@approve');
    Route::post('transfer-items/{id}/reject', 'TransferItem\\TransferItemApprovalController@reject');
    Route::post('transfer-items/{id}/cancellation-approve', 'TransferItem\\TransferItemCancellationApprovalController@approve');
    Route::post('transfer-items/{id}/cancellation-reject', 'TransferItem\\TransferItemCancellationApprovalController@reject');
    Route::apiResource('transfer-items', 'TransferItem\\TransferItemController');
    Route::post('transfer-items/export', 'TransferItem\\TransferItemController@export');
    Route::get('transfer-items/{id}/histories', 'TransferItem\\TransferItemHistoryController@index');
    Route::post('transfer-items/histories', 'TransferItem\\TransferItemHistoryController@store');
    Route::apiResource('receive-items', 'TransferItem\\ReceiveItemController');
});
