<?php

Route::prefix('human-resource')->namespace('HumanResource')->group(function () {
    Route::prefix('kpi')->namespace('Kpi')->group(function () {
        Route::apiResource('templates', 'KpiTemplateController');
        Route::apiResource('template-groups', 'KpiTemplateGroupController');
        Route::apiResource('template-indicators', 'KpiTemplateIndicatorController');
        Route::apiResource('template-scores', 'KpiTemplateScoreController');
        Route::get('results/showBy', 'KpiResultController@showBy');
        Route::apiResource('results', 'KpiResultController');
        Route::apiResource('automated', 'KpiAutomatedController');
    });

    Route::prefix('employee')->namespace('Employee')->group(function () {
        Route::apiResource('groups', 'EmployeeGroupController');
        Route::apiResource('religions', 'EmployeeReligionController');
        Route::apiResource('marital-statuses', 'EmployeeMaritalStatusController');
        Route::apiResource('genders', 'EmployeeGenderController');
        Route::apiResource('statuses', 'EmployeeStatusController');
        Route::apiResource('job-locations', 'EmployeeJobLocationController');
        Route::apiResource('employees', 'EmployeeController');
        Route::apiResource('user-employee', 'UserEmployeeController');
        Route::post('employees/{employee_id}/assign-assessment', 'AssignAssessmentController@store');
        Route::apiResource('employees/{employee_id}/assessment', 'EmployeeAssessmentController');
        Route::get('employees/{employee_id}/salary/assessment', 'EmployeeSalaryController@assessment');
        Route::get('employees/{employee_id}/salary/achievement', 'EmployeeSalaryController@achievement');
        Route::apiResource('employees/{employee_id}/salary', 'EmployeeSalaryController');
    });
});
