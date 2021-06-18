<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Creditcard extends Model
{
    protected $table = "creditcards";

    public function patient(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
