<?php

Route::prefix('manufacture')->namespace('Manufacture')->group(function () {
    Route::apiResource('/machines', 'MachineController');
    Route::apiResource('/processes', 'ProcessController');
    Route::post('formulas/{id}/approve', 'FormulaApprovalController@approve');
    Route::post('formulas/{id}/reject', 'FormulaApprovalController@reject');
    Route::post('formulas/{id}/cancellation-approve', 'FormulaCancellationApprovalController@approve');
    Route::post('formulas/{id}/cancellation-reject', 'FormulaCancellationApprovalController@reject');
    Route::apiResource('/formulas', 'FormulaController');
    Route::post('input-materials/{id}/approve', 'InputMaterialApprovalController@approve');
    Route::post('input-materials/{id}/reject', 'InputMaterialApprovalController@reject');
    Route::post('input-materials/{id}/cancellation-approve', 'InputMaterialCancellationApprovalController@approve');
    Route::post('input-materials/{id}/cancellation-reject', 'InputMaterialCancellationApprovalController@reject');
    Route::apiResource('/input-materials', 'InputMaterialController');
    Route::apiResource('/output-products', 'OutputProductController');
});
