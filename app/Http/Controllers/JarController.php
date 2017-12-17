<?php

namespace App\Http\Controllers;

use App\Jar;
use Illuminate\Http\Request;
use App\Http\Resources\UserJar;
use App\Jobs\ExtractJarEntrypoints;
use Illuminate\Contracts\Auth\Guard;

class JarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Guard $auth)
    {
        $user = $auth->user();

        return UserJar::collection(
            $user->jars()->paginate(25)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $jar = new Jar(['user_id' => 1]);
        $jar->saveOrFail();

        $jar->addMediaFromRequest('jar')
            ->toMediaCollection();

        dispatch(new ExtractJarEntrypoints($jar->media()->first()));

        return UserJar::make($jar->media->first());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Jar  $jar
     * @return \Illuminate\Http\Response
     */
    public function show(Jar $jar)
    {
        $jar->load('media.model');

        return UserJar::collection($jar->media);
    }
}
