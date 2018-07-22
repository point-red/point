<?php

Route::prefix('master')->namespace('Master')->group(function () {
    Route::apiResource('users', 'UserController');
    Route::apiResource('user-invitations', 'UserInvitationController');

    Route::apiResource('warehouses', 'WarehouseController');
});
