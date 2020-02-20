<?php

Route::prefix('manufacture')->namespace('Manufacture')->group(function () {
    Route::apiResource('/machines', 'MachineController');
    Route::apiResource('/processes', 'ProcessController');
    Route::post('formulas/{id}/approve', 'FormulaApprovalController@approve');
    Route::post('formulas/{id}/reject', 'FormulaApprovalController@reject');
    Route::apiResource('/formulas', 'FormulaController');
    Route::apiResource('/input-materials', 'InputMaterialController');
    Route::apiResource('/output-products', 'OutputProductController');
});
