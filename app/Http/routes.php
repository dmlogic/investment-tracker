<?php
Route::group(['middleware' => ['auth.basic']], function () {
    Route::get( '/',                           ['as' => 'home', 'uses' => 'Controller@homepage'] );
    Route::get( '/group/{group}/fund/{fund}',  ['as' => 'fund', 'uses' => 'Controller@fund'] );
});
