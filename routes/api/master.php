<?php

Route::prefix('master')->namespace('Master')->group(function () {
    // User, Role and Permission
    Route::apiResource('users', 'UserController');
    Route::get('roles/{roles_id}/permissions', 'RolePermissionController@index');
    Route::patch('roles/{roles_id}/permissions', 'RolePermissionController@togglePermission');
    Route::patch('roles/{roles_id}/permissions/bulk-update', 'RolePermissionController@bulkUpdate');
    Route::apiResource('roles', 'RoleController');
    Route::apiResource('user-invitations', 'UserInvitationController');
    Route::apiResource('user-roles', 'UserRoleController');

    // Master
    Route::apiResource('groups', 'GroupController');
    Route::apiResource('warehouses', 'WarehouseController');
    Route::apiResource('items', 'ItemController');
    // Route::apiResource('services', 'ServiceController');
    Route::apiResource('customers', 'CustomerController');
    Route::apiResource('suppliers', 'SupplierController');
    // Route::apiResource('employees', 'EmployeeController');
    // Route::apiResource('kpi/templates', 'KpiTemplateController');
    // Route::apiResource('expeditions', 'ExpeditionController');
    // Route::apiResource('allocations', 'AllocationController');
    // Route::apiResource('allocations', 'AllocationController');
});
