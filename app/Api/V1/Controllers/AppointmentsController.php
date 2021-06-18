<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Api\V1\Requests\BookAppointmentRequest;
use App\Api\V1\Requests\ConfirmAppointmentRequest;
use App\Api\V1\Requests\CancelAppointmentRequest;
use App\Api\V1\Requests\ProviderScheduleRequest;
use App\Api\V1\Requests\PatientScheduleRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\Subuser;
use App\Timeslot;
use App\Appointment;
use App\Cancellation;
use App\Communications;
use App\Qualification;
use App\Rating;
use JWTAuth;
use DateTime;
use DateTimeZone;
use DateInterval;

class AppointmentsController extends Controller {

    private $APPOINTMENT_PER_PAGE = 3;

    public function book ( BookAppointmentRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $timeslotID = $request->get('timeslot');
        $timeslot = Timeslot::find( $timeslotID );
        if ( !$timeslot ) { throw new HttpException(404); }
        if ( $timeslot->status != 'open' ) { throw new HttpException(400); }

        $position = $timeslot->provider->qualifications->first()->position;
        // $providerQualified = Qualification::where([
        //                                     ['user_id', $timeslot->provider_id],
        //                                     ['position_id', $positionID]
        //                                 ])->exists();

        if ( !$position ) { throw new HttpException(400); }

        $appointment = new Appointment();
        $appointment->position_id = $position->id;
        $appointment->patient_id = $currentUser->id;
        $appointment->time_slot_id = $timeslot->id;
        $appointment->address = $request->address;

        if ( $request->has('for_someone_else') && $request->for_someone_else) {
            $appointment->for_someone_else = $this->getSubUser($currentUser->id, $request->patient);
        }
        
        if ( $request->has('note') ) {
            $appointment->note = $request->get('note');
        }

        if ( !$appointment->save() ) {
            throw new HttpException(500);
        }

        $timeslot->status = 'booked';
        if ( !$timeslot->save() ) {
            $appointment->delete();
            throw new HttpException(500);
        }

        $this->sendBookingCommunications( $appointment );

        return response()->json([
            'status' => 'ok'
        ], 201);
    }

    private function sendBookingCommunications ( $appointment ) {
        $patientData = array(
            'email_subject' => 'Appointment Booked',
            'email_body' => 'You have booked an appointment',
        );
        $providerData = array(
            'email_subject' => 'Appointment Booked',
            'email_body' => 'A patient has booked an appointment',
            'sms_message' => 'A patient has booked an appointment'
        );
        $providerID = $appointment->timeslot->provider_id;
        Communications::privateEmail( $appointment->patient_id, $patientData['email_subject'], $patientData['email_body'] );
        Communications::privateEmail( $providerID, $providerData['email_subject'], $providerData['email_body'] );
        Communications::sendSMS( $providerID, $providerData['sms_message'] );
        Communications::sendMessage( $appointment->patient_id, false, $patientData['email_subject'], $patientData['email_body'] );
    }

    public function confirm ( ConfirmAppointmentRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $appointmentID = $request->get('appointment');
        $appointment = Appointment::find( $appointmentID );
        if ( !$appointment ) { throw new HttpException(404); }
        if ( $currentUser->id != $appointment->timeslot->provider_id ) { throw new HttpException(403); }

        $appointment->status = 'confirmed';
        if ( !$appointment->save() ) { throw new HttpException(500); }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    public function getCancellationInfo ( Request $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $appointmentID = $request->get('appointment');
        $appointment = Appointment::find( $appointmentID );
        if ( !$appointment ) { throw new HttpException(404); }
        $timeslot = $appointment->timeslot;
        if ( !$timeslot ) { throw new HttpException(404); }

        if ( $currentUser->role->role != 'admin' && !in_array( $currentUser->id, array( $appointment->timeslot->provider_id, $appointment->patient_id ) ) ) {
            throw new HttpException(403);
        }

        $timeZone = new DateTimeZone( 'America/Toronto' );
        $now = ( new DateTime() )->setTimeZone( $timeZone );
        $appointmentStart = new DateTime( $timeslot->start, $timeZone );
        $appointmentCreated = ( new DateTime( $appointment->created_at ))->setTimeZone( $timeZone );

        if ( $appointmentStart < $now ) {
            throw new HttpException(400);
        }
        $fee = 0;
        if ( $currentUser->role->role == 'patient' ) {
            $timeFromNow = $appointmentStart->diff( $now );
            $timeFromCreation = $now->diff( $appointmentCreated );
            
            $hours = $timeFromNow->h + $timeFromNow->days*24;
            $minutes = $timeFromNow->i + $hours*60;
            if ( $hours < 4 ) {
                $fee = 100;
            } elseif ( $minutes > 30 ) {
                $fee = 50;
            }
            $data = array(
                "fee" => $fee
            );
        }

        return response()->json([
            'status' => 'ok',
            'data' => $data
        ], 200);
    }

    public function cancel ( CancelAppointmentRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $appointmentID = $request->get('appointment');
        $appointment = Appointment::find( $appointmentID );
        if ( !$appointment ) { throw new HttpException(404); }
        $timeslot = $appointment->timeslot;
        if ( !$timeslot ) { throw new HttpException(404); }

        if ( $currentUser->role->role != 'admin' && !in_array( $currentUser->id, array( $appointment->timeslot->provider_id, $appointment->patient_id ) ) ) {
            throw new HttpException(403);
        }
        
        $timeZone = new DateTimeZone( 'America/Toronto' );
        $now = ( new DateTime() )->setTimeZone( $timeZone );
        $appointmentStart = new DateTime( $timeslot->start, $timeZone );

        if ( $appointmentStart < $now ) {
            throw new HttpException(400);
        }

        $timeslot->status = 'open';
        if ( !$timeslot->save() ) {
            throw new HttpException(500);
        }

        $appointment->status = 'cancelled';
        if ( !$appointment->save() ) {
            throw new HttpException(500);
        }

        if ( $currentUser->role->role == 'patient' ) {
            $timeFromNow = $appointmentStart->diff( $now );
            $this->patientCancellation( $appointment, $timeFromNow, $request->get('amount'), $request->get('token') );
        } elseif ( $currentUser->role->role == 'provider' ) {
            $this->providerCancellation( $appointment );
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    private function patientCancellation ( $appointment, $timeFromNow, $amount, $token ) {
        $cancellation = new Cancellation();
        if($amount != 0){
            $cancellation->amount = $amount;
            $cancellation->token = $token;
        }
        $appointment->cancellation()->save($cancellation);
        $patientData = array(
            'email_subject' => 'Appointment Cancellation',
            'email_body' => 'You have cancelled your appointment',
        );
        $providerData = array(
            'email_subject' => 'Appointment Cancellation',
            'email_body' => 'Your patient has cancelled your appointment',
            'sms_message' => 'Your patient has cancelled your appointment'
        );
        $providerID = $appointment->timeslot->provider_id;
        Communications::privateEmail( $appointment->patient_id, $patientData['email_subject'], $patientData['email_body'] );
        Communications::privateEmail( $providerID, $providerData['email_subject'], $providerData['email_body'] );
        Communications::sendSMS( $providerID, $providerData['sms_message'] );
        Communications::sendMessage( $appointment->patient_id, false, $patientData['email_subject'], $patientData['email_body'] );
    }

    private function providerCancellation ( $appointment ) {
        //todo: add auto-negative review
        
        $patientData = array(
            'email_subject' => 'Appointment Cancellation',
            'email_body' => 'Your provider has cancelled your appointment',
            'sms_message' => 'Your provider has cancelled your appointment'
        );
        $providerData = array(
            'email_subject' => 'Appointment Cancellation',
            'email_body' => 'You have cancelled your appointment',
        );
        $providerID = $appointment->timeslot->provider_id;
        Communications::privateEmail( $appointment->patient_id, $patientData['email_subject'], $patientData['email_body'] );
        Communications::privateEmail( $providerID, $providerData['email_subject'], $providerData['email_body'] );
        Communications::sendSMS( $appointment->patient_id, $patientData['sms_message'] );
        Communications::sendMessage( $providerID, false, $providerData['email_subject'], $providerData['email_body'] );
    }

    public function queryProviderSchedule ( ProviderScheduleRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $date = $request->get('date');

        $timeslotQuery = Timeslot::where([
                                    ['provider_id', $currentUser->id],
                                    ['date', $date]
                                ])
                                ->with([
                                    'appointment' => function ( $query ) {
                                        $query->with([
                                                'patient' => function ( $query ) {
                                                    $query->select(
                                                            'id',
                                                            'first_name',
                                                            'last_name',
                                                            'phone_number',
                                                            'address',
                                                            'city',
                                                            'province',
                                                            'postal_code'
                                                        );
                                                }
                                            ]);
                                    }
                                ])
                                ->orderBy('start', 'asc');
        
        $schedule = $timeslotQuery->get();
        
        return response()->json([
            'status' => 'ok',
            'data' => $schedule
        ], 200);
    }

    public function queryPatientSchedule ( PatientScheduleRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        //todo: add ratings when implemented
        $scheduleQuery = Timeslot::whereHas('appointment', function ( $query ) use ( $currentUser ) {
                                    $query->where( 'patient_id', $currentUser->id );
                                })
                                ->with([
                                    'appointment' => function ( $query ) use ( $currentUser ) {
                                        $query->with([
                                            'position' => function ( $query ) {
                                                $query->select(
                                                    'id',
                                                    'position'
                                                );
                                            },
                                            'patient' => function ( $query ) {
                                                $query->select(
                                                    'id',
                                                    'first_name',
                                                    'last_name',
                                                    'address',
                                                    'city',
                                                    'province',
                                                    'postal_code'
                                                );
                                            },
                                            'rating' => function ( $query ) use ( $currentUser ) {
                                                $query->where('rater_id', $currentUser->id);
                                            },
                                            'subuser',
                                        ])
                                        ->select(
                                            'id',
                                            'time_slot_id',
                                            'position_id',
                                            'patient_id',
                                            'paid',
                                            'address',
                                            'for_someone_else',
                                            'status',
                                            'created_at'
                                        );
                                    },
                                    'provider' => function ( $query ) {
                                        $query->select(
                                            'id',
                                            'first_name',
                                            'last_name'
                                        );
                                    },
                                ])
                                ->select(
                                    'id',
                                    'provider_id',
                                    'start',
                                    'end'
                                )
                                ->orderBy( 'start', 'asc' );

        $schedule = $scheduleQuery->get();

        return response()->json([
            'status' => 'ok',
            'data' => $schedule
        ], 200);
    }

    private function getSubUser($patientID, $subuser){
        $patient = Subuser::where("user_id", $patientID)
                          ->where("first_name", $subuser["first_name"])
                          ->where("last_name", $subuser["last_name"])
                          ->where("email", $subuser["email"])
                          ->where("phone_number", $subuser["phone_number"])
                          ->where("DOB", $subuser["DOB"])
                          ->where("MOB", $subuser["MOB"])
                          ->where("YOB", $subuser["YOB"])
                          ->where("healthcard", $subuser["healthcard"])
                          ->first();
        if($patient){
            return $patient->id;
        }
        else{
            $newSubUser = new Subuser();
            $user = User::find($patientID);

            $newSubUser->users()->associate($user);
            $newSubUser->first_name = $subuser["first_name"];
            $newSubUser->last_name = $subuser["last_name"];
            $newSubUser->email = $subuser["email"];
            $newSubUser->phone_number = $subuser["phone_number"];
            $newSubUser->DOB = $subuser["DOB"];
            $newSubUser->MOB = $subuser["MOB"];
            $newSubUser->YOB = $subuser["YOB"];
            $newSubUser->healthcard = $subuser["healthcard"];

            $newSubUser->save();
            return $newSubUser->id;
        }
    }

    public function loadAppointments(Request $request){
        if($request->has("userid")){
            $user = User::find($request->userid);
            $role = $user->role->role;
            $page = $request->page ? $request->page : 1;
    
            $appointments = Appointment::where($role."_id", $user->id)
                                       ->with(["rating" =>  function($query) use ($user){
                                            $query->where("rater_id", $user->id);
                                       },
                                       "provider", "patient", "timeslot"])
                                       ->offset(($page-1) * $this->APPOINTMENT_PER_PAGE)
                                       ->limit($this->APPOINTMENT_PER_PAGE)
                                       ->get();
    
            return response()->json([
                'status' => 'ok',
                'data' => $appointments
            ], 200);
        }
    }

    public function removeFeedback(Request $request){
        if($request->has("rating")){
            $rating = Rating::find($request->rating);
            $rating->delete();

            return response()->json([
                'status' => 'ok',
            ], 200);
        }
    }

    public function savePatientRating(Request $request){
        $currentUser = JWTAuth::parseToken()->authenticate();
        $appointment = $currentUser->appointmentPatient()
                                   ->where("appointments.id", $request->appointment)
                                   ->first();

        $rating = new Rating();
        $rating->appointment_id = $appointment->id;
        $rating->rater_id = $currentUser->id;
        $rating->rated_id = $appointment->provider->id;
        $rating->rating = $request->rating;
        $rating->feedback = $request->feedback;

        if ( !$rating->save() ) { throw new HttpException(404); }
        return response()->json([
            'status' => 'ok'
        ], 201);
    }
}