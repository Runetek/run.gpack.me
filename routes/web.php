<?php

use Illuminate\Http\Request;
use App\Jar;
use App\Jobs\ExtractJarEntrypoints;
use App\Http\Resources\UserJar;

Route::get('/', function () {
    return view('welcome');
});

Route::get('jars/{jar}', function (Jar $jar) {
    $jar->load('media.model');

    return UserJar::collection($jar->media);
});

Route::post('jars', function (Request $request) {
    $request->validate([
        'jar' => [
            'required',
            'file',
            'max:10240',
            'mimetypes:application/java-archive,application/zip',
            'mimes:jar',
        ],
    ]);
    $jar = new Jar(['user_id' => 1]);
    $jar->saveOrFail();

    $jar->addMediaFromRequest('jar')
        ->toMediaCollection();

    dispatch(new ExtractJarEntrypoints($jar->media->first()));

    return $jar->load('media');
});
