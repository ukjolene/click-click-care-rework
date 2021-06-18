<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = "positions";

    public function users(){
        return $this->belongsToMany('App\User', 'qualifications', 'position_id', 'user_id');
    }

    public function appointments(){
        return $this->hasMany('App\Appointment');
    }

    public function qualifications(){
        return $this->hasMany('App\Qualification');
    }
}
