<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table = "ratings";

    public function appointment(){
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }

    public function rater(){
        return $this->belongsTo('App\User', 'rater_id');
    }

    public function rated(){
        return $this->belongsTo('App\User', 'rated_id');
    }
}
