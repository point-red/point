<?php

Route::prefix('reward')->namespace('Reward')->group(function () {
    Route::resource('point', 'PointController')->only(['index', 'show']);
});
