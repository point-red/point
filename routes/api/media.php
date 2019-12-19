<?php

Route::prefix('media')->namespace('Media')->group(function () {
    Route::get('/employee/{id}', 'MediaController@mediaEmployee');
    Route::post('/upload/employee', 'MediaController@mediaEmployeeStore');
    Route::post('/update', 'MediaController@mediaEmployeeUpdate');
    Route::get('/download/{id}', 'MediaController@download');
    Route::get('/delete/{id}', 'MediaController@destroy');
});