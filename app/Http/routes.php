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

Route::get('video-info/{videoId}', function ($videoId) {
    return file_get_contents("https://www.youtube.com/get_video_info?&video_id={$videoId}&asv=3&el=detailpage&hl=en_US");
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
        return json_encode(array('key' => $match->key));
    } else {
        // Find an available match if not found
        $match = Match::available()->first();
        if(!$match) {
            // Create a new match if not found
            $key = str_random();
            $match = factory(App\Match::class)->create(array(
                'key' => $key,
                'state' => 'WAITING_FOR_PLAYERS'
            ));
            $match->save();
        }
        $user->matches()->attach($match->id, ['state' => 'WAITING_FOR_PLAYERS']);

        return json_encode(array('key' => $match->key));
    }
});

Route::get('match/start/{key}/{username}', function ($key, $username) {
    $user = User::where('name', $username)->first();

    $match = Match::where('key', $key)->where('state', '!=', ['FINISHED'])->first();
    if($match) {
        $match->state = 'STARTED';
        $match->save();

        $matches = $user->matches();
        $matches->detach($match->id);
        $playerNumber = count($match->users()->where('match_user.state', 'PLAYING')->get()) + 1;
        $matches->attach($match->id, ['state' => 'PLAYING', 'player_number' => $playerNumber]);

        return json_encode(array('player_number' => $playerNumber));
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
