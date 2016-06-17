<?php

use App\Match;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('match/{key}', function ($key) {
    $matches = Match::all();
    if(count($matches) == 0) {
        $match = factory(App\Match::class)->create(array(
            "key" => $key
        ));
        $match->save();
        return "WAITING";
    }
    $match = $matches->first();
    $match->delete();
    return $match->key;
});
