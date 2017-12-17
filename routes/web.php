<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('oauth', 'RunetekSSOController@redirect');
Route::get('oauth/callback', 'RunetekSSOController@callback');
