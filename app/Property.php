<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = "properties";
    //
    public function values()
    {
        return $this->hasMany('App\Value');
    }
    public function roles(){
        return $this->belongsToMany('App\Role', 'rel_role_property', 'property_id', 'role_id');
    }
}
