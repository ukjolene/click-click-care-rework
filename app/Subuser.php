<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subuser extends Model
{
    protected $table = "subusers";

    public function users(){
        return $this->belongsTo('App\User', 'user_id');
    }

    public function appointments(){
        return $this->hasMany('App\Appointment', 'for_someone_else');
    }
}
