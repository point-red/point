<?php

Route::prefix('media')->group(function () {
    Route::get('/employee/{id}', 'MediaController@mediaEmployee');
    Route::post('/upload/employee', 'MediaController@mediaEmployeeStore');
    Route::get('/download/{id}', 'MediaController@download');
    Route::get('/delete/{id}', 'MediaController@destroy');
});