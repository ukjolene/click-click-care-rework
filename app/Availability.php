<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Availability extends Model
{
    use SoftDeletes;

    protected $table = "availabilities";

    public function provider () {
        return $this->belongsTo('App\User', 'provider_id');
    }

}
