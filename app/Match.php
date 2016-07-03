<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    public function scopeAvailable($query, $playerCount) {
        return $query->join('match_user', 'match_user.match_id', '=', 'matches.id')
            ->groupBy('match_user.match_id')
            ->selectRaw('matches.*, count(match_user.user_id) as count')
            ->having('count', '<', $playerCount);
    }

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot(['player_number']);
    }
}
