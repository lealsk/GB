<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    public function scopeAvailable($query, $playerCount = null) {
        $query->where('state', 'WAITING_FOR_PLAYERS');
        if($playerCount) {
            $query->join('match_user', 'match_user.match_id', '=', 'matches.id')
                ->groupBy('match_user.match_id')
                ->selectRaw('matches.*, count(match_user.user_id) as count')
                ->having('count', '<', $playerCount);
        }
        return $query;
    }

    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot(['state']);
    }
}
