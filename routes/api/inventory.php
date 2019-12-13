<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('inventories', 'InventoryController@index');
    Route::apiResource('inventory-audits', 'InventoryAudit\\InventoryAuditController');
    // Route::apiResource('inventory-usages', 'InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    // Route::apiResource('transfer-items', 'TransferItemController');
});
