<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('/', 'InventoryController@index');
    Route::get('/{item_id}', 'InventoryController@show');
    Route::apiResource('inventory-audits', 'InventoryAudit\\AuditController');
    // Route::apiResource('inventory-usages', 'InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    // Route::apiResource('transfer-items', 'TransferItemController');
});
