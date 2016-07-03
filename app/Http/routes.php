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
        $match = Match::available(2)->first();
        if($match) {
            $playerNumber = "2";
        } else {
            // Create a new match if not found
            $key = str_random();
            $match = factory(App\Match::class)->create(array(
                'key' => $key
            ));
            $match->save();
            $playerNumber = "1";
        }
        $user->matches()->attach($match->id, ['player_number' => $playerNumber]);

        // TODO: Fix for avoiding reconnecting always to the same match. Remove this part once we implement match finished logic
        if($playerNumber == "2") {
            $match->users()->sync([]);
            $match->delete();
        }

        return json_encode(array('key' => $match->key, 'player_number' => $playerNumber));
    }
});
