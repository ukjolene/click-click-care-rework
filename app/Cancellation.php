<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cancellation extends Model
{
    use SoftDeletes;

    protected $table = "cancellations";

    public function appointment(){
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }
}
