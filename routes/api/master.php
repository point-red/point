<?php

Route::prefix('master')->namespace('Master')->group(function () {
    Route::apiResource('users', 'UserController');

    Route::apiResource('person-categories', 'PersonCategoryController');
    Route::apiResource('person-groups', 'PersonGroupController');
    Route::apiResource('persons', 'PersonController');

    Route::apiResource('warehouses', 'WarehouseController');
});
