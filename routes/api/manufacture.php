<?php

Route::prefix('manufacture')->namespace('Manufacture')->group(function () {
    Route::apiResource('/machines', 'MachineController');
    Route::apiResource('/processes', 'ProcessController');
    Route::apiResource('/formulas', 'FormulaController');
    Route::apiResource('/input-materials', 'InputMaterialController');
    Route::apiResource('/output-products', 'OutputProductController');
});
