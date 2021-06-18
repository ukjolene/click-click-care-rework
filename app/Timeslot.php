<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeslot extends Model
{
    use SoftDeletes;
    
    protected $table = "timeslots";

    public function provider(){
        return $this->belongsTo('App\User', 'provider_id');
    }

    public function appointment(){
        return $this->hasOne('App\Appointment', 'time_slot_id' );
    }

}
