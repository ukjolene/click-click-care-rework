<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $table = "offices";

    protected $fillable = [
        'user_id', 'address', 'city', 'postal_code', 'phone_number', 'latitude', 'longitude'
    ];

    public function provider(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
