<?php

use App\Match;
use App\User;
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

Route::get('match/{username}', function ($username) {
    $user = User::where('name', $username)->first();

    // Creates temporary guest user if not found
    if(!$user) {
        $user = factory(App\User::class)->create(array(
            'name' => $username,
            'password' => null,
            'email' => null,
            'guest' => true
        ));
    }

    // Return current match if found
    $match = $user->matches()->first();
    if($match) {
        return json_encode(array('key' => $match->key, 'player_number' => $match->pivot->player_number));
    } else {
        // Find an available match if not found
        // TODO: Find a better way of supporting more than 2 players per match
        $match = Match::available(2)->first();
        if($match) {
            $playerNumber = "2";
        } else {
            // Create a new match if not found
            $key = str_random();
            $match = factory(App\Match::class)->create(array(
                'key' => $key,
                'state' => 'WAITING_FOR_PLAYERS'
            ));
            $match->save();
            $playerNumber = "1";
        }
        $user->matches()->attach($match->id, ['player_number' => $playerNumber]);

        return json_encode(array('key' => $match->key, 'player_number' => $playerNumber));
    }
});

Route::get('match/start/{key}', function ($key) {
    $match = Match::where('key', $key)->where('state', 'WAITING_FOR_PLAYERS')->first();
    if($match) {
        $match->state = 'STARTED';
        $match->save();
    }
});

Route::get('match/end/{key}', function ($key) {
    $match = Match::where('key', $key)->where('state', 'STARTED')->first();
    if($match) {
        $match->state = 'FINISHED';
        $match->save();
    }
});

Route::get('match/cancel/{key}', function ($key) {
    $match = Match::where('key', $key)->first();
    if($match) {
        $match->state = 'CANCELLED';
        $match->save();
    }
});

// TODO: find a better approach for non many vs many matches
Route::get('match/reset/{key}/{username}', function ($key, $username) {
    $user = User::where('name', $username)->first();

    $match = Match::where('key', $key)->first();
    if($match) {
        $match->state = 'WAITING_FOR_PLAYERS';
        $match->users()->sync([]);
        $match->users()->attach($user->id, ['player_number' => 1]);
        $match->save();
    }
});
