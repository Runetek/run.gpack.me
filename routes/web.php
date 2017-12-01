<?php

use Illuminate\Http\Request;
use App\Jar;
use App\Jobs\ExtractJarEntrypoints;
use App\Http\Resources\UserJar;
use App\Http\Requests\UploadJar;

Route::get('/', function () {
    return view('welcome');
});

Route::get('jars/{jar}', function (Jar $jar) {
    $jar->load('media.model');

    return UserJar::collection($jar->media);
});

Route::post('jars', function (UploadJar $request) {
    $jar = new Jar(['user_id' => 1]);
    $jar->saveOrFail();

    $jar->addMediaFromRequest('jar')
        ->toMediaCollection();

    dispatch(new ExtractJarEntrypoints($jar->media()->first()));

    return UserJar::make($jar->media->first());
});
