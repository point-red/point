<?php

Route::prefix('manufacture')->namespace('Manufacture')->group(function () {
    Route::apiResource('/machine', 'MachineController');
    Route::apiResource('/process', 'ProcessController');
    Route::apiResource('/formula', 'FormulaController');
    // Route::apiResource('/input-material', 'InputMaterialController');
    // Route::apiResource('/output-product', 'OutputProductController');
});
