<?php

namespace App;

use Hash;
use Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'email', 'password', 'first_name', 'last_name', 'gender', 'address', 'city', 'province', 'postal_code', 'phone_number', 'latitude', 'longitude'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    public function role(){
        return $this->belongsTo('App\Role');
    }

    public function avatar(){
        return $this->hasOne('App\Avatar');
    }

    public function creditcard(){
        return $this->hasOne('App\Creditcard');
    }

    public function office(){
        return $this->hasOne('App\Office');
    }

    public function getAvatar64Attribute () {
        if ( !$this->avatar ) { return null; }
        $userAvatar = $this->avatar->avatar;
        $file = Storage::get("avatar/" . $userAvatar);
        $base64str = base64_encode($file);
        $extArr = explode(".", $userAvatar);
        $ext = end($extArr);
        switch ($ext){
            case "jpg":
            case "jpeg":
                $type = "image/jpeg";
                break;
            case "png":
                $type = "image/png";
                break;
            case "gif":
                $type = "image/gif";
                break;
            case "ico":
                $type = "image/x-icon";
                break;
            case "tif":
            case "tiff":
                $type = "image/tif";
                break;
        }
        return (object)array(
            'type' => $type,
            'image' => $base64str
        );
    }

    public function positions(){
        return $this->belongsToMany('App\Position', 'qualifications', 'user_id', 'position_id');
    }

    public function appointmentProvider(){
        return $this->hasMany('App\Appointment', 'provider_id');
    }

    public function appointmentPatient(){
        return $this->hasMany('App\Appointment', 'patient_id');
    }

    public function messagesReceived(){
        return $this->hasMany('App\Message', 'recipient_id');
    }

    public function messagesSent(){
        return $this->hasMany('App\Message', 'sender_id');
    }

    public function values(){
        return $this->hasMany('App\Value');
    }

    public function qualifications(){
        return $this->hasMany('App\Qualification');
    }

    public function rated(){
        return $this->hasOne('App\Rating', 'rated_id');
    }

    public function rater(){
        return $this->hasOne('App\Rating', 'rater_id');
    }

    public function subusers(){
        return $this->hasMany('App\Subuser', 'user_id');
    }

    public function timeslots(){
        return $this->hasMany('App\Timeslot', 'provider_id');
    }

    public function rating(){
        return $this->hasOne('App\RatingView','id');
    }

    public function view(){
        return $this->hasOne('App\UserView','id');
    }
}
