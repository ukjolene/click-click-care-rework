<?php

use Dingo\Api\Routing\Router;


/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    // Publicly Accessible Endpoints
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signupaspatient', 'App\\Api\\V1\\Controllers\\SignUpController@signUpAsPatient');
        $api->post('signupasprovider', 'App\\Api\\V1\\Controllers\\SignUpController@signUpAsProvider');

        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        // Predefined Language List
        $api->get('getpredefinedlanguages', 'App\\Api\\V1\\Controllers\\SignUpController@getPredefinedLanguages');
        // Predefined Positions
        $api->get('getpredefinedpositions', 'App\\Api\\V1\\Controllers\\SignUpController@getPredefinedPositions');
        $api->get('loaduserprofile', 'App\\Api\\V1\\Controllers\\ProfileController@loadUserProfile');
        $api->post('changepassword', 'App\\Api\\V1\\Controllers\\ProfileController@changePassword');
        $api->post('searchproviders', 'App\\Api\\V1\\Controllers\\ProfileController@searchProviders');
        $api->post('gettimeslot', 'App\\Api\\V1\\Controllers\\TimeSlotsController@getTimeslot');
    });

    // Publicly Accessible Endpoints
    $api->group(['prefix' => 'profile', 'middleware' => 'api.auth'], function(Router $api) {
        $api->post('updateuserprofile', 'App\\Api\\V1\\Controllers\\ProfileController@updateUserProfile');
        $api->post('loadproviderprofile', 'App\\Api\\V1\\Controllers\\ProfileController@loadProviderProfile');
        $api->group(['middleware' => 'isprovider'], function ( Router $api ) {
            $api->post('loadpatientprofile', 'App\\Api\\V1\\Controllers\\ProfileController@loadPatientProfile');
        });
        $api->get('getuseraddress', 'App\\Api\\V1\\Controllers\\ProfileController@getUserProvider');
        $api->group(['middleware' => 'isadmin'], function ( Router $api ) {
            $api->post('adminloadprofile', 'App\\Api\\V1\\Controllers\\ProfileController@adminLoadProfile');
        });
    });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return response()->json([
                'message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.'
            ]);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });


    /*************/
    /*   Messages  */
    /*************/

    $api->group(['prefix' => 'message','middleware' => 'api.auth'], function(Router $api){
        $api->post('send', 'App\\Api\\V1\\Controllers\\SendMessageController@send');
        $api->post('delete', 'App\\Api\\V1\\Controllers\\DeleteMessageController@delete');
        $api->post('load', 'App\\Api\\V1\\Controllers\\LoadMessageController@load');
        $api->post('markread', 'App\\Api\\V1\\Controllers\\ListMessageController@markAsRead');
        $api->post('unreadmessage', 'App\\Api\\V1\\Controllers\\ListMessageController@unreadMessage');
        $api->post('listreceived', 'App\\Api\\V1\\Controllers\\ListMessageController@messagesReceived');
        $api->post('listsent', 'App\\Api\\V1\\Controllers\\ListMessageController@messagesSent');
    });


    $api->group(['prefix' => 'users','middleware' => 'api.auth'], function ($api) {        
        $api->post('searchuser', 'App\\Api\\V1\\Controllers\\ProfileController@searchuser');
    });


    /******************/
    /*  Appointments  */
    /******************/

    $api->group(['prefix' => 'appointments', 'middleware' => 'api.auth' ], function ( Router $api ) {

        $api->post('cancel', 'App\\Api\\V1\\Controllers\\AppointmentsController@cancel');
        $api->post('getcancellationinfo', 'App\\Api\\V1\\Controllers\\AppointmentsController@getCancellationInfo');
        $api->post('filtertimeslots', 'App\\Api\\V1\\Controllers\\TimeSlotsController@filterTimeSlots');

        $api->group(['middleware' => 'isprovider'], function ( Router $api ) {
            $api->post('confirm', 'App\\Api\\V1\\Controllers\\AppointmentsController@confirm');
            $api->post('block', 'App\\Api\\V1\\Controllers\\TimeSlotsController@blockTimeSlot');
            $api->post('setavailability', 'App\\Api\\V1\\Controllers\\TimeSlotsController@setAvailability');
            $api->post('providerschedule', 'App\\Api\\V1\\Controllers\\AppointmentsController@queryProviderSchedule');
        });

        $api->group(['middleware' => 'ispatient'], function ( Router $api ) {
            $api->post('bookingdetails', 'App\\Api\\V1\\Controllers\\TimeSlotsController@loadBookingDetails');
            $api->post('book', 'App\\Api\\V1\\Controllers\\AppointmentsController@book');
            $api->post('patientschedule', 'App\\Api\\V1\\Controllers\\AppointmentsController@queryPatientSchedule');
            $api->post('savepatientrating', 'App\\Api\\V1\\Controllers\\AppointmentsController@savePatientRating');
        });

        $api->group(['middleware' => 'isadmin'], function ( Router $api ) {
            $api->post('loadapppointments', 'App\\Api\\V1\\Controllers\\AppointmentsController@loadAppointments');
            $api->post('removefeedback', 'App\\Api\\V1\\Controllers\\AppointmentsController@removeFeedback');
        });

    });

    /******************/
    /*  Admin  */
    /******************/

    $api->group(['prefix' => 'admin', 'middleware' => 'api.auth' ], function ( Router $api ) {

        $api->post('listcancellations', 'App\\Api\\V1\\Controllers\\AdminController@getListCancellations');
    
    });

     /******************/
    /*  User  */
    /******************/

    $api->group(['prefix' => 'user', 'middleware' => 'api.auth' ], function ( Router $api ) {

        $api->post('listusers', 'App\\Api\\V1\\Controllers\\UserController@getListUsers');
        $api->post('updatestatus', 'App\\Api\\V1\\Controllers\\UserController@updateStatus');

    });

    $api->group(['prefix' => 'schedule', 'middleware' => 'api.auth' ], function ( Router $api ) {
        $api->group(['middleware' => 'isprovider'], function ( Router $api ) {
            $api->post('loadschedule', 'App\\Api\\V1\\Controllers\\ScheduleController@getProviderSchedule');
            $api->post('blocktimeslot', 'App\\Api\\V1\\Controllers\\ScheduleController@blockTimeslot');
            $api->post('unblocktimeslot', 'App\\Api\\V1\\Controllers\\ScheduleController@unblockTimeslot');
            $api->post('cancelappointment', 'App\\Api\\V1\\Controllers\\ScheduleController@cancelAppointment');
            $api->post('loadshifts', 'App\\Api\\V1\\Controllers\\ScheduleController@loadShifts');
            $api->post('saveshifts', 'App\\Api\\V1\\Controllers\\TimeSlotsController@setAvailability');
        });

    });


});
