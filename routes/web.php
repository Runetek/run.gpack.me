<?php

use Illuminate\Http\Request;
use App\Jar;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('jars', function (Request $request) {
    $jar = new Jar(['user_id' => 1]);
    $jar->saveOrFail();
    $jar->addMediaFromRequest('jar')
        ->toMediaCollection();
    return $jar->load('media');
});
