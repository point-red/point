<?php

Route::prefix('inventory')->namespace('Inventory')->group(function () {
    Route::get('/', 'InventoryController@index');
    Route::apiResource('inventory-audits', 'InventoryAudit\\InventoryAuditController');
    // Route::apiResource('inventory-usages', 'InventoryUsageController');
    // Route::apiResource('inventory-corrections', 'InventoryCorrectionController');
    Route::apiResource('transfer-send', 'TransferSendItemController');
    Route::apiResource('transfer-receive', 'TransferReceiveItemController');
    Route::get('/stock', 'InventoryController@getStock');
});
