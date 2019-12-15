<?php

Route::prefix('pos')->namespace('Pos')->group(function () {
    Route::apiResource('/bill', 'BillController');
});
