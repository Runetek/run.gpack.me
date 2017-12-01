<?php

use Illuminate\Http\Request;
use App\Jar;
use App\Jobs\ExtractJarEntrypoints;

Route::get('/', function () {
    return view('welcome');
});

Route::post('jars', function (Request $request) {
    $jar = new Jar(['user_id' => 1]);
    $jar->saveOrFail();
    $jar->addMediaFromRequest('jar')
        ->toMediaCollection();
    dispatch(new ExtractJarEntrypoints($jar->media->first()));
    return $jar->load('media');
});
