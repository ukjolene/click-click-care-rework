<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Cancellation;
use App\Appointment;
use App\Communications;
use JWTAuth;
use DateTime;
use DateTimeZone;
use DateInterval;

class AdminController extends Controller {

    public function getListCancellations ( Request $request ) {
        //we identify the user using the token
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $page = ($request->get('page') != "") ? $request->get('page') : 1;
        $request['page'] = "";

        
        //Retrieve all cancellations
        $cancellations = Appointment::with([
                'cancellation',
                'timeslot',
                'provider',
                'patient',
                'rating'
            ]
        )
        ->where('status','cancelled');

        //cancelledByFilter
        if($request->has('cancelledByFilter')){
            if($request->get('cancelledByFilter') != ""){
                if($request->get('cancelledByFilter') == "3"){
                    $cancellations = $cancellations->has('cancellation');
                }elseif($request->get('cancelledByFilter') == "2"){
                    $cancellations = $cancellations->doesntHave('cancellation');
                }
            }
        }


        $cancellations = $cancellations->paginate(15*$page)->toArray();
        
        //return paginated data
        return response()->json([
            'status' => 'ok',
            'data' => $cancellations
        ], 201);

    }

}