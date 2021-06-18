<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Timeslot;
use App\Appointment;
use App\Availability;
use App\Communications;
use App\Rating;
use DB;
use JWTAuth;

class ScheduleController extends Controller
{

    private $RECORD_PER_PAGE = 5;

    public function getProviderSchedule(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();

        $page = $request->page ? $request->page : 1;
        $timeslots = Timeslot::where("provider_id", $currentUser->id)
                             ->where("date", $request->date)
                             ->with(["appointment" => function($query){
                                 $query->where("status", "confirmed")
                                       ->with("patient")
                                       ->with("rating");
                             }])
                             ->offset(($page-1) * $this->RECORD_PER_PAGE)
                             ->limit($this->RECORD_PER_PAGE)
                             ->get();
        
        return response()->json([
            'status' => 'ok',
            'timeslots' => $timeslots
        ], 201);
    }

    public function blockTimeslot(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();
        $timeslot = Timeslot::where("id", $request->timeslot)
                             ->where("provider_id", $currentUser->id)
                             ->first();
        
        if ( !$timeslot ) { throw new HttpException(404); }
        $timeslot->status = "blocked";

        if ( !$timeslot->update() ) { throw new HttpException(404); }

        return response()->json([
            'status' => 'ok'
        ], 201);
    }

    public function unblockTimeslot(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();
        $timeslot = Timeslot::where("id", $request->timeslot)
                             ->where("provider_id", $currentUser->id)
                             ->first();
        
        if ( !$timeslot ) { throw new HttpException(404); }
        $timeslot->status = "open";

        if ( !$timeslot->update() ) { throw new HttpException(404); }
        return response()->json([
            'status' => 'ok'
        ], 201);
    }

    public function cancelAppointment(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();

        // $appointment = Appointment::where("id", $request->appointmentID)
        //                           ->where("provider_id", $currentUser->id)
        //                           ->first();
        $appointment = Appointment::find($request->appointmentID);
        
        $appointment->status = "cancelled";
        
        $appointment->update();
        
        $timeslot = $appointment->timeslot;
        $timeslot->status = "open";
        $timeslot->update();

        $admin = User::where("role_id", 1)
                     ->first();

        $rating = new Rating();
        $rating->rating = 0;
        $rating->feedback = "Provider cancelled appointment";
        $rating->appointment()->associate($appointment);
        $rating->rater()->associate($admin);
        $rating->rated()->associate($currentUser);
        $rating->save();

        $this->sendAppointmentCancelNotification($appointment);

        return response()->json([
            'status' => 'ok'
        ], 201);
    }

    private function sendAppointmentCancelNotification($appointment){
        $patientData = array(
            'email_subject' => 'Appointment Cancelled',
            'email_body' => 'Your appointment has been cancelled by provider.',
            'sms_message' => 'Your appointment has been cancelled by provider.'
        );
        $providerData = array(
            'email_subject' => 'Appointment Cancelled',
            'email_body' => 'You have cancelled an appointment',
        );
        $providerID = $appointment->timeslot->provider_id;
        Communications::privateEmail( $providerID, $providerData['email_subject'], $providerData['email_body'] );
        Communications::privateEmail( $appointment->patient_id, $patientData['email_subject'], $patientData['email_body'] );
        Communications::sendSMS( $appointment->patient_id, $patientData['sms_message'] );
        Communications::sendMessage( $appointment->patient_id, false, $patientData['email_subject'], $patientData['email_body'] );
    }

    public function loadShifts(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();
        $dates = $request->dates;
        if(sizeof($dates)==0){
            return response()->json([
                'status' => 'ok',
                'data' => [],
            ], 201);

        }
        else if(sizeof($dates)==1){
            $shift = Availability::where("provider_id", $currentUser->id)
                                  ->where("date", $request->dates)
                                  ->get();
            return response()->json([
                'status' => 'ok',
                'data' => $shift,
            ], 201);
        }
        else{
            $isTimeDurationSame = true;
            $shifts = DB::table('availabilities')
                       ->where("provider_id", $currentUser->id)
                       ->whereIn("date", $request->dates)
                       ->groupBy("start", "end")               
                       ->select([
                           DB::raw('count(*) as count'),
                           'start',
                           'end',
                       ])
                       ->get()
                       ->toArray();
            foreach($shifts as $shift){
                if($shift->count != sizeof($dates)){
                    $isTimeDurationSame = false;
                    break;
                }
                else{
                    $shift->duration = $this->getDuration($shift, $currentUser->id, $dates[0]);
                }
            }
            if($isTimeDurationSame == true){
                return response()->json([
                    'status' => 'ok',
                    'data' => $shifts,
                ], 201);
            }
            else{
                return response()->json([
                    'status' => 'conflict',
                    'data' => $shifts,
                ], 201);
            }
        }

    }

    private function getDuration($shift, $provider_id, $date){
        $data = Availability::where("provider_id", $provider_id)
                            ->where("start", $shift->start)
                            ->where("end", $shift->end)
                            ->where("date", $date)
                            ->first();
        return $data->duration;
    }
}
