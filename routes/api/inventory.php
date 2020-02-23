<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('inventories', 'InventoryController@index');
    Route::get('inventories/{itemId}/warehouses', 'InventoryController@indexDetail');
    Route::get('inventories/{itemId}', 'InventoryController@show');
    Route::get('inventories/{itemId}/dna', 'InventoryController@dna');
    Route::apiResource('inventory-audits', 'InventoryAudit\\InventoryAuditController');
    // Route::apiResource('inventory-usages', 'InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    // Route::apiResource('transfer-items', 'TransferItemController');
});
