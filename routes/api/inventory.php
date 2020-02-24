<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('inventory-recapitulations', 'InventoryRecapitulationController@index');
    Route::get('inventory-warehouse-recapitulations/{itemId}', 'InventoryWarehouseRecapitulationController@index');
    Route::get('inventory-details/{itemId}', 'InventoryDetailController@index');
    Route::get('inventory-dna/{itemId}', 'InventoryDnaController@index');
    Route::apiResource('inventory-audits', 'InventoryAudit\\InventoryAuditController');
    // Route::apiResource('inventory-usages', 'InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    // Route::apiResource('transfer-items', 'TransferItemController');
});
