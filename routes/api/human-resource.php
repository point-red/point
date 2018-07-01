<?php

Route::prefix('human-resource')->namespace('HumanResource')->group(function () {
    Route::prefix('kpi')->namespace('Kpi')->group(function () {
        Route::apiResource('templates', 'KpiTemplateController');
        Route::apiResource('template-groups', 'KpiTemplateGroupController');
        Route::apiResource('template-indicators', 'KpiTemplateIndicatorController');
        Route::apiResource('template-scores', 'KpiTemplateScoreController');
        Route::get('results/showBy', 'KpiResultController@showBy');
        Route::apiResource('results', 'KpiResultController');
        Route::apiResource('kpis', 'KpiController');
        Route::apiResource('groups', 'KpiGroupController');
        Route::apiResource('indicators', 'KpiIndicatorController');
    });

    Route::prefix('employee')->namespace('Employee')->group(function () {
        Route::apiResource('groups', 'EmployeeGroupController');
        Route::apiResource('employees', 'EmployeeController');
        Route::apiResource('employees/{employee_id}/assessment', 'EmployeeAssessmentController');
    });
});
