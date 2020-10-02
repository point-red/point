<?php

use Illuminate\Support\Facades\Route;

Route::prefix('plugin')->namespace('Plugin')->group(function () {
    Route::prefix('salary-non-sales')->name('salary-non-sales.')->namespace('SalaryNonSales')->group(function () {
        Route::apiResource('groups', 'GroupController');
        Route::apiResource('group-factors', 'GroupFactorController');
        Route::apiResource('factor-criterias', 'FactorCriteriaController');
        Route::patch('job-locations/{id}', 'JobLocationController@update');
    });
});
