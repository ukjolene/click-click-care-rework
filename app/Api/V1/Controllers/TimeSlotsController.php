<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Api\V1\Requests\SetAvailabilityRequest;
use App\Api\V1\Requests\FilterTimeSlotsRequest;
use App\Api\V1\Requests\BlockTimeSlotRequest;
use App\Api\V1\Requests\LoadBookingDetailsRequest;
use App\Http\Controllers\Controller;
use App\User;
use App\Timeslot;
use App\Availability;
use JWTAuth;
use DateTime;
use DateTimeZone;
use DateInterval;

class TimeSlotsController extends Controller {

    public function setAvailability ( SetAvailabilityRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $days = $request->get('days');
        $duration = $request->get('duration');
        $shifts = $request->get('shifts');

        $this->resetAvailabilities( $currentUser, $days, $duration, $shifts );

        $timeSlots = ( $shifts ) ? $this->calculateTimeSlots( $shifts, $duration ) : [];

        foreach ( $days as $day ) {

            // delete unused slots due to potential changes in times/durations
            Timeslot::where([
                ['provider_id', $currentUser->id],
                ['date', $day],
                ['status', '!=', 'booked']
            ])->delete();

            foreach ( $timeSlots as $timeSlot ) {
                $startDateTime = $day.' '.$timeSlot['start'];
                $endDateTime = $day.' '.$timeSlot['end'];

                // check that slot does not overlap previously booked slot
                $slotUnassignable = Timeslot::where([
                    ['provider_id', $currentUser->id],
                    ['date', $day],
                ])->where(function($query) use ($startDateTime, $endDateTime) {
                    $query->whereBetween('start', array($startDateTime, $endDateTime))
                        ->where([
                            ['start', '!=', $startDateTime],
                            ['start', '!=', $endDateTime]
                        ]);
                })->orWhere(function($query) use ($startDateTime, $endDateTime) {
                    $query->whereBetween('end', array($startDateTime, $endDateTime))
                        ->where([
                            ['end', '!=', $startDateTime],
                            ['end', '!=', $endDateTime]
                        ]);
                })->first();

                if ( $slotUnassignable ) { continue; }

                $newSlot = new Timeslot();
                $newSlot->provider_id = $currentUser->id;
                $newSlot->date = $day;
                $newSlot->start = $startDateTime;
                $newSlot->end = $endDateTime;

                if ( !$newSlot->save() ) {
                    throw new HttpException(500);
                }
            }
        }

        return response()->json([
            'status' => 'ok'
        ], 201);
    }

    private function resetAvailabilities ( $currentUser, $days, $duration, $shifts ) {
        Availability::where( 'provider_id', $currentUser->id )
                    ->whereIn( 'date', $days )
                    ->delete();
        if ( !$shifts ) { return; }
        foreach ( $days as $day ) {
            foreach ( $shifts as $shift ) {
                $availability = new Availability();
                $availability->provider_id = $currentUser->id;
                $availability->date = $day;
                $availability->start = $shift['start'];
                $availability->end = $shift['end'];
                $availability->duration = $duration;
                if ( !$availability->save() ) {
                    throw new HttpException( 500 );
                }
            }
        }
    }

    private function calculateTimeSlots ( $shifts, $durationMinutes ) {
        $timeZone = new DateTimeZone( 'America/Toronto' );
        $duration = new DateInterval('PT'.$durationMinutes.'M');
        $dummyDate = '2000-01-01';
        $timeSlots = array();
        foreach ( $shifts as $shift ) {
            $lastSlotEnd = false;
            $endDateTime = new DateTime( $dummyDate.' '.$shift['end'].':00', $timeZone );
            $startDateTime = new DateTime( $dummyDate.' '.$shift['start'].':00', $timeZone );
            $testDateTime = clone $startDateTime;
            $testDateTime->add( $duration );
            while ( $testDateTime <= $endDateTime ) {
                $startTime = ( $lastSlotEnd ) ? $lastSlotEnd : $startDateTime->format('H:i:s');
                $lastSlotEnd = $testDateTime->format('H:i:s');
                $timeSlots[] = array(
                    'start' => $startTime,
                    'end' => $lastSlotEnd
                );
                $testDateTime->add( $duration );
            }
        }
        return $timeSlots;
    }

    public function loadBookingDetails ( LoadBookingDetailsRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $timeslotID = $request->get('timeslot');
        $query = Timeslot::where([
                                ['id', $timeslotID],
                                ['status', 'open']
                            ]);
        
        if ( !$query->exists() ) {
            throw new HttpException(500, 'Timeslot does not exists or is not available');
        }

        $query->with([
                    'provider' => function ( $query ) {
                        $query->select([
                            'id',
                            'first_name',
                            'last_name'
                        ]);
                    }
                ])
                ->select([
                    'id',
                    'provider_id',
                    'start'
                ]);

        $timeslot = $query->first();
        
        $userData = $currentUser;

        return response()->json([
            'status' => 'ok',
            'data' => [
                'timeslot' => $timeslot,
                'patient' => $userData
            ],
            'user' => $currentUser
        ]);
    }

    public function filterTimeSlots ( FilterTimeSlotsRequest $request ) {
        $timeZone = new DateTimeZone( 'America/Toronto' );
        $today = ( new DateTime() )->setTimeZone( $timeZone )->format('Y-m-d');

        $dateFilter = $request->get('date');

        $timeSlotsQuery = User::where('approved', 'approved')
                                ->with([
                                    'timeslots' => function ( $query ) use ( $dateFilter ) {
                                        $query->where('date', $dateFilter)
                                        ->orderBy( 'start' );
                                    }
                                ])
                                ->whereHas('timeslots', function ( $query ) use ( $dateFilter ) {
                                    $query->where([
                                        ['date', $dateFilter],
                                        ['status', 'open']
                                    ]);
                                })
                                ->select([
                                    'id',
                                    'first_name',
                                    'last_name',
                                    'gender',
                                ]);
        // todo: add position/ratings/languages/etc when features implemented
        $timeSlots = $timeSlotsQuery->get();

        return response()->json([
            'status' => 'ok',
            'data' => $timeSlots
        ], 200);
    }

    public function blockTimeSlot ( BlockTimeSlotRequest $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $timeSlotID = $request->get('timeslot');

        $timeSlot = Timeslot::find( $timeSlotID );
        if ( !$timeSlot ) { throw new HttpException(404); }

        $status = ( $request->get('block') || !$request->has('block') ) ? 'blocked' : 'open';
        $timeSlot->status = $status;
        if ( !$timeSlot->save() ) {
            throw new HttpException(500);
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    public function getTimeslot(Request $request){
        $timeslotID = $request->get('timeslot');
        $query = Timeslot::where([
                                ['id', $timeslotID],
                                ['status', 'open']
                            ]);
        
        if ( !$query->exists() ) {
            throw new HttpException(500, 'Timeslot does not exists or is not available');
        }

        $query->with([
                    'provider' => function ( $query ) {
                        $query->select([
                            'id',
                            'first_name',
                            'last_name'
                        ]);
                    }
                ])
                ->select([
                    'id',
                    'provider_id',
                    'start'
                ]);

        $timeslot = $query->first();

        return response()->json([
            'status' => 'ok',
            'data' => $timeslot,
        ]);
    }

}