<?php

Route::prefix('human-resource')->namespace('HumanResource')->group(function () {
    Route::prefix('kpi')->namespace('Kpi')->group(function () {
        Route::put('templates/{id}/archive', 'KpiTemplateController@archive');
        Route::patch('templates/{id}/archive', 'KpiTemplateController@archive');
        Route::put('templates/bulk-archive', 'KpiTemplateController@bulkArchive');
        Route::patch('templates/bulk-archive', 'KpiTemplateController@bulkArchive');
        Route::put('templates/{id}/activate', 'KpiTemplateController@activate');
        Route::patch('templates/{id}/activate', 'KpiTemplateController@activate');
        Route::put('templates/bulk-activate', 'KpiTemplateController@bulkActivate');
        Route::patch('templates/bulk-activate', 'KpiTemplateController@bulkActivate');
        Route::put('templates/bulk-delete', 'KpiTemplateController@bulkDelete');
        Route::patch('templates/bulk-delete', 'KpiTemplateController@bulkDelete');

        Route::apiResource('templates', 'KpiTemplateController');
        Route::post('templates/copy-group', 'KpiTemplateController@copyGroup');
        Route::post('templates/duplicate', 'KpiTemplateController@duplicate');
        Route::apiResource('template-groups', 'KpiTemplateGroupController');
        Route::apiResource('template-indicators', 'KpiTemplateIndicatorController');
        Route::apiResource('template-scores', 'KpiTemplateScoreController');
        Route::get('results/showBy', 'KpiResultController@showBy');
        Route::apiResource('results', 'KpiResultController');
        Route::apiResource('automated', 'KpiAutomatedController');
        Route::post('templates/export', 'KpiTemplateExportController@export');
        Route::post('templates/import/check', 'KpiTemplateImportController@check');
        Route::post('templates/import', 'KpiTemplateImportController@import');
    });

    Route::prefix('employee')->namespace('Employee')->group(function () {
        Route::apiResource('groups', 'EmployeeGroupController');
        Route::apiResource('religions', 'EmployeeReligionController');
        Route::apiResource('marital-statuses', 'EmployeeMaritalStatusController');
        Route::apiResource('genders', 'EmployeeGenderController');
        Route::apiResource('statuses', 'EmployeeStatusController');
        Route::apiResource('job-locations', 'EmployeeJobLocationController');
        Route::apiResource('additional-components', 'EmployeeSalaryAdditionalComponentController');
        Route::put('employees/{id}/archive', 'EmployeeController@archive');
        Route::patch('employees/{id}/archive', 'EmployeeController@archive');
        Route::put('employees/bulk-archive', 'EmployeeController@bulkArchive');
        Route::patch('employees/bulk-archive', 'EmployeeController@bulkArchive');
        Route::put('employees/{id}/activate', 'EmployeeController@activate');
        Route::patch('employees/{id}/activate', 'EmployeeController@activate');
        Route::put('employees/bulk-activate', 'EmployeeController@bulkActivate');
        Route::patch('employees/bulk-activate', 'EmployeeController@bulkActivate');
        Route::put('employees/bulk-delete', 'EmployeeController@bulkDelete');
        Route::patch('employees/bulk-delete', 'EmployeeController@bulkDelete');
        Route::apiResource('employees', 'EmployeeController');
        Route::post('employees/{employee_id}/assign-assessment', 'AssignAssessmentController@store');
        Route::apiResource('employees/{employee_id}/assessment', 'EmployeeAssessmentController');
        Route::get('employees/{employee_id}/assessment-by/{group}', 'EmployeeAssessmentController@showBy');
        Route::get('employees/{employee_id}/salary/assessment', 'EmployeeSalaryController@assessment');
        Route::get('employees/{employee_id}/salary/achievement', 'EmployeeSalaryController@achievement');
        Route::apiResource('employees/{employee_id}/salary', 'EmployeeSalaryController');
        Route::get('employees/{employee_id}/salary-by/{group}', 'EmployeeSalaryController@showBy');
        Route::post('employees/{employee_id}/salary/export', 'EmployeeSalaryExportController@export');
        Route::post('employees/upload', 'EmployeeAssessmentController@upload');
        Route::get('employees/file/{file_name}', 'EmployeeAssessmentController@file');
        Route::post('employees/kpi-reminder', 'EmployeeAssessmentController@kpiReminder');
        Route::post('employees/contract-reminder', 'EmployeeAssessmentController@contractReminder');
    });
});
