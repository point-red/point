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

    // Warehouse
    Route::apiResource('warehouses', 'WarehouseController');

    // Group
    Route::apiResource('groups', 'GroupController');

    // Vendor
    Route::apiResource('customers', 'CustomerController');
    // Route::apiResource('supplier', 'SupplierController');
    // Route::apiResource('expedition', 'ExpeditionController');
});
