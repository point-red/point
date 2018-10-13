<?php

Route::prefix('master')->namespace('Master')->group(function () {
    Route::apiResource('users', 'UserController');
    Route::get('roles/{roles_id}/permissions', 'RolePermissionController@index');
    Route::patch('roles/{roles_id}/permissions', 'RolePermissionController@togglePermission');
    Route::patch('roles/{roles_id}/permissions/bulk-update', 'RolePermissionController@bulkUpdate');
    Route::apiResource('roles', 'RoleController');
    Route::apiResource('user-invitations', 'UserInvitationController');
    Route::apiResource('user-roles', 'UserRoleController');

    Route::apiResource('warehouses', 'WarehouseController');

    Route::apiResource('customer-groups', 'CustomerGroupController');
    Route::apiResource('customers', 'CustomerController');
});
