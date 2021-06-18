<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $table='qualifications';
    
    public function position() {
        return $this->belongsTo('App\Position', 'position_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Position', 'position_id', 'id');
    }
}
