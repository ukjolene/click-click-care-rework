<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Api\V1\Requests\LoadProviderProfileRequest;
use App\Api\V1\Requests\LoadPatientProfileRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\Appointment;
use App\Timeslot;
use App\Position;
use DB;
use JWTAuth;
use DateTime;
use DateTimeZone;
use DateInterval;

class ProfileController extends Controller
{

    private $MAX_LOAD_USER;
    private $PROVIDER_PER_PAGE = 5;

    private function getUserProfile ( $userID ) {
        $user = User::find( $userID );
        foreach($user->values as $value){
            $user->{$value->property->property} = $value->value;
        }
        unset($user->values);
        return $user;
    }

    private function getProviderProfile($userID){
        $user = User::where('id', $userID)
                    ->with('office')
                    ->with('qualifications.position')
                    ->with('values')
                    ->first();
        foreach($user->values as $value){
            $user->{$value->property->property} = $value->value;
        }
        unset($user->values);
        return $user;

    }

    public function loadUserProfile () {
        $currentUser = JWTAuth::parseToken()->authenticate();
        switch ($currentUser->role_id){
            case '2':
                $profile = $this->getProviderProfile($currentUser->id);
                break;
            case '3':
                $profile = $this->getUserProfile( $currentUser->id );
                break;
        }
        return response()->json([
            'status' => 'ok',
            'data' => $profile
        ], 201);
    }

    public function adminLoadProfile(Request $request){
        $user = User::where("id", $request->userid)->first();
        switch ($user->role_id){
            case '2':
                $profile = $this->getProviderProfile($user->id);
                break;
            case '3':
                $profile = $this->getUserProfile( $user->id );
                break;
        }
        return response()->json([
            'status' => 'ok',
            'data' => $profile
        ], 201);        
    }

    public function loadProviderProfile ( LoadProviderProfileRequest $request ) {
        $providerID = $request->get('id');
        $profile = $this->getUserProfile( $providerID );
        if ( !$profile || $profile->role->role != 'provider' ) { throw new HttpException(404); }

        $profile->positions = $profile->positions()->pluck('position');

        return response()->json([
            'status' => 'ok',
            'data' => $profile
        ], 201);
    }

    public function loadPatientProfile ( LoadPatientProfileRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $patientID = $request->get('id');

        // check patient exists
        $patientExists = User::where('id', $patientID)
                            ->whereHas('role', function ( $query ) {
                                $query->where('role', 'patient');
                            })
                            ->exists();
        if ( !$patientExists ) { throw new HttpException(404); }

        // check patient visible to provider
        $profileVisible = Appointment::where([
                                        ['patient_id', $patientID]
                                        //todo: check appointment date?
                                    ])
                                    ->whereHas('timeslot', function ( $query ) use ( $currentUser ) {
                                        $query->where( 'provider_id', $currentUser->id );
                                    })
                                    ->exists();
        if ( !$profileVisible ) { throw new HttpException(403); }

        // check other provider's notes visible to current provider
        $timeZone = new DateTimeZone( 'America/Toronto' );
        $now = ( new DateTime() )->setTimeZone( $timeZone )->format('Y-m-d H:i:s');
        $notesVisible = Appointment::where( 'patient_id', $patientID )
                                    ->whereHas('timeslot', function ( $query ) use ( $now, $currentUser ) {
                                            $query->where([
                                                ['end', '>=', $now],
                                                ['provider_id', $currentUser->id]
                                            ]);
                                    })
                                    ->exists();

        // get user profile
        $profile = User::find( $patientID );
        foreach ( $profile->values as $value ) {
            $profile->{ $value->property->property } = $value->value;
        }
        unset( $profile->values );

        // attach appointment data
        $appointmentsQuery = Timeslot::whereHas('appointment', function ( $query ) use ( $profile ) {
                                        $query->where('patient_id', $profile->id);
                                        $query->whereNotNull('note');
                                    })
                                    ->with([
                                        'appointment' => function ( $query ) {
                                            $query->select( 'id', 'time_slot_id', 'patient_id', 'provider_id', 'position_id', 'note' );
                                            $query->with([
                                                'position' => function ( $query ) {
                                                    $query->select( 'id', 'position' );
                                                }
                                            ]);
                                        },
                                        'provider' => function ( $query ) {
                                            $query->select( 'id', 'first_name', 'last_name' );
                                        }
                                    ])
                                    ->select( 'id', 'provider_id', 'start', 'end' )
                                    ->orderBy( 'start', 'desc' );

        if ( !$notesVisible ) {
            // hide other doctors' appointments
            $appointmentsQuery->where( 'provider_id', $currentUser->id );
        }

        $profile->appointments = $appointmentsQuery->get();

        return response()->json([
            'status' => 'ok',
            'data' => $profile
        ]);
    }

    public function updateUserProfile(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();

        $currentUser->first_name = $request->get('first_name') ? $request->get('first_name') : $currentUser->first_name;
        $currentUser->last_name = $request->get('last_name') ? $request->get('last_name') : $currentUser->last_name;
        $currentUser->gender = $request->get('gender') ? $request->get('gender') : $currentUser->gender;
        $currentUser->phone_number = $request->get('phone_number') ? $request->get('phone_number') : $currentUser->phone_number;
        $currentUser->address = $request->get('address') ? $request->get('address') : $currentUser->address;
        $currentUser->city = $request->get('city') ? $request->get('city') : $currentUser->city;
        $currentUser->province = $request->get('province') ? $request->get('province') : $currentUser->province;
        $currentUser->postal_code = $request->get('postal_code') ? $request->get('postal_code') : $currentUser->postal_code;
        $currentUser->phone_number = $request->get('phone_number') ? $request->get('phone_number') : $currentUser->phone_number;

        if(!$currentUser->update()) {
            throw new HttpException(500);
        }
        else{
            $role = $currentUser->role;

            foreach($role->properties as $property){
                $value = $property->values()->where('user_id', $currentUser->id)->first();
                // if($property->property == 'resume'){
                //     if($request->resume !== null){
                //         if($request->hasFile('resume') && $request->file('resume')->isValid()){
                //             // // file will be saved @storage/app/resumes
                //             $filename = 'user_'.$currentUser->id.'_resume.'.$request->resume->extension();
                //             $folderpath = "resume";
                //             Storage::delete($folderpath . '/' . $value->value);
                //             $path = $request->resume->storeAs($folderpath, $filename);
                //             $value->value = $filename;
                //         }
                //     }
                //     else{
                //         continue;
                //     }
                // }
                // else{
                    if($request->get($property->property) ){
                        $value->value = $request->get($property->property);
                    }
                    else{
                        continue;
                    }
                // }
                $value->update();
            }
            if($role->id == "2"){
                //update office
                $office = $currentUser->office;
                $office->address = $request->office['address'] ? $request->office['address'] : $office->address;
                $office->city = $request->office['city'] ? $request->office['city'] : $office->city;
                $office->province = $request->office['province'] ? $request->office['province'] : $office->province;
                $office->postal_code = $request->office['postal_code'] ? $request->office['postal_code'] : $office->postal_code;
                $office->latitude = $request->office['latitude'] ? $request->office['latitude'] : $office->latitude;
                $office->longitude = $request->office['longitude'] ? $request->office['longitude'] : $office->longitude;
                $office->update();

                //update qualification
                $qualifications = $currentUser->qualifications;
                foreach($qualifications as $qualification){
                    $qualification->position_id = $request->qualifications['position_id'] ? $request->qualifications['position_id'] : $qualification->position_id;
                    $qualification->update();
                }
            }

            // $this->updateAvatar($request, $currentUser);
        }        
        return response()->json([
            'status' => 'ok',
            'data' => []
        ]);
    }

    /**
     *
     * Load a list of users with id/email/fullname
     *
     * @Post("/api/user/searchuser")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"}, body={"name": "string", "module": "string"})
     * @Response(201, body={"user: user"})
     *
     */

    public function searchuser(Request $request, JWTAuth $JWTAuth){
        $currentUser = JWTAuth::parseToken()->authenticate();
        if( !$currentUser ) {
             throw new HttpException(500);
        }
        if ( $request->get('module') == 'message' ){
            //directly return a user if an exactly matched email has been found 
            $user = User::where('email', $request->get('name'))->get()->first();
            if( count( $user ) != 0 ) {
                if( $currentUser->id == $user->id ){
                    // not return self as a result
                    return response()->json([
                        'status' => 'ok',
                        'data' => []
                    ], 201);
                }  

                $userData = [[
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->first_name." ".$user->last_name
                ]];
                return response()->json([
                    'status' => 'ok',
                    'data' => $userData
                ], 201);
            }

            $usersQuery = DB::table('views_user')
                            ->where('fullname', 'like', '%'.$request->get('name').'%')
                            ->orWhere( function( $query ) use ($request) {
                                $query->where('email', 'like', '%'.$request->get('name').'%');
                            });
            //check user role
            switch ( $currentUser->role_id ) {
                case 1:
                    // admin
                    break;
                case 2:
                    // provider
                    $today = ( new DateTime() )->setTimeZone( new DateTimeZone('America/Toronto') );
                    $oneDay = new DateInterval('P1D');
                    $yesterday = ( clone $today );
                    $tomorrow = ( clone $today );
                    $yesterday->sub( $oneDay )->format('Y-m-d H:i:s');
                    $tomorrow->add( $oneDay )->format('Y-m-d H:i:s');
                    $usersQuery->where('role_id', '=', 3)
                               ->whereExists( function ( $query ) use ( $currentUser, $yesterday, $tomorrow ) {
                                    $query->select(DB::raw(1))
                                        ->from('appointments')
                                        ->join('timeslots', 'appointments.time_slot_id', '=', 'timeslots.id')
                                        ->where([
                                            ['timeslots.provider_id', '=', $currentUser->id],
                                            ['appointments.patient_id', '=', 'users.id'],
                                        ])
                                        ->whereBetween('timeslots.date', [ $yesterday, $tomorrow ]);
                                });
                    break;
                case 3:
                    // patient
                    $usersQuery->where('role_id', '=', 2)
                               ->whereExists(function ( $query ) {
                                    $query->select(DB::raw(1))
                                        ->from('appointments')
                                        ->join('timeslots', 'appointments.time_slot_id', '=', 'timeslots.id')
                                        ->where([
                                            ['timeslots.provider_id', '=', 'users.id'],
                                            ['appointments.patient_id', '=', $currentUser->id]
                                        ]);
                                });
                    break;
            }

            $usersFound = $usersQuery->limit( $this->MAX_LOAD_USER )
                                     ->get(['id', 'fullname as name', 'email'])
                                     ->toArray();

            return response()->json([
                'status' => 'ok',
                'data' => $usersFound
            ], 201);
        }
        
    }

    public function changePassword(Request $request, JWTAuth $JWTAuth){

        $currentUser = JWTAuth::parseToken()->authenticate();
        // If an admin user changing the password of another admin
        if($currentUser->role_id == 1 && $request->has('user_id')) {
            $currentUser = User::find($request->user_id);
        }

        $password = $request->newpassword;
        $confirm = $request->confirmpassword;
        if($password === $confirm){
            $currentUser->password = $password;
            if($currentUser->save()){
                return response()->json([
                    'status' => 'ok'
                ], 201);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'failed to update password'
                ], 201);
            }
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => 'passwords not match'
            ], 201);
        }

    }

    public function getUserProvider(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => 'ok',
            'address' => $currentUser->address
        ], 201);
    }

    public function searchProviders(Request $request){
        $page = $request->page ? $request->page : 1;

        $query = User::with(['values', 'qualifications.position', 'rating']);

        // Position filter
        $query = $query->whereHas("qualifications", function ($query) use ($request){
            $query->where("position_id", $request->position);
        });

        // Date filter
        $query = $query->whereHas("timeslots", function($query) use ($request){
            $query->where("date", $request->date)
                  ->where("status", "open");
        });
        $query = $query->with(["timeslots" => function($query) use ($request){
            $query->where("date", $request->date)
                  ->where("status", "open");
        }]);

        // Language filter
        $query = $query->whereHas("values", function($query) use ($request){
            $query->where("property_id", 3)
                  ->where("value", "like", "%$request->language%");
        });

        // Gender filter
        if($request->gender != "none"){
            $query->where("gender", $request->gender);
        }

        if($request->name != ""){
            $query->whereIn("id", function($query) use ($request){
                $query->from('views_user')
                      ->select("id")
                      ->where("fullname", "like", "%$request->name%");
            });
        }
            
        $lat = $request->lat;
        $long = $request->long;
        $userIDs = [];

        //queries for users that are within the radius provided then pushs user ids into an array. 
        //userlist then returns filtered results against ids found in array and passses to listusers function
        $users  = DB::select(
              DB::raw(
                "SELECT `offices`.`user_id`, `values`.`value`, GEODIST(`offices`.`latitude`, `offices`.`longitude`, ?,? ) AS distance
                FROM `offices` LEFT JOIN `values` ON `offices`.`user_id`=`values`.`user_id` WHERE `property_id`=4"
              ),[$lat, $long]);

        foreach($users as $user){
            $distance = $user->distance ? $user->distance : 0;
            if($user->value >= $distance){
                array_push($userIDs, $user->user_id);
            }
        }

        $providers = $query->whereIn('id', $userIDs) 
                           ->offset(($page-1) * $this->PROVIDER_PER_PAGE)
                           ->limit($this->PROVIDER_PER_PAGE)
                           ->get();

        foreach ($providers as $key => $provider) {
            $provider->avatarimg = $provider->getAvatar64Attribute();
        }

        return response()->json([
            'status' => 'ok',
            'data' => $providers
        ], 201);
    }

}
