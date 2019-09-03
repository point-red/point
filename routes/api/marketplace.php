<?php

Route::prefix('marketplace')->namespace('Marketplace')->group(function () {
    Route::get('items', 'MarketPlaceItemsController@index');
});
