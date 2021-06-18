<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $table = "appointments";

    public function position(){
        return $this->belongsTo('App\Position', 'position_id');
    }

    public function provider(){
        return $this->belongsTo('App\User', 'provider_id');
    }

    public function patient(){
        return $this->belongsTo('App\User', 'patient_id');
    }

    public function rating(){
        return $this->hasMany('App\Rating', 'appointment_id');
    }

    public function cancellation(){
        return $this->hasOne('App\Cancellation', 'appointment_id');
    }

    public function timeslot(){
        return $this->belongsTo('App\Timeslot', 'time_slot_id' );
    }

    public function subuser(){
        return $this->belongsTo('App\Subuser', 'for_someone_else' );
    }

}
