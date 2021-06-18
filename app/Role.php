<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";

    public function users(){
        return $this->hasMany('App\User');
    }

    public function properties(){
        return $this->belongsToMany('App\Property', 'rel_role_property', 'role_id', 'property_id');
    }
}
