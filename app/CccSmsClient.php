<?php

namespace App;

use App\User;
use Twilio\Rest\Client;

class CccSmsClient {

    public static function sendSMS ( $toUsers, $message, $fromNumber = false ) {

        if ( !is_array( $toUsers ) ) {
            $toUsers = array( $toUsers );
        }

        $toPhoneNumbers = array_map( 'self::getPhoneNumbers', $toUsers );
        $fromNumber = ( $fromNumber ) ? $fromNumber : env('TWILIO_FROM_NUMBER', false);

        $sid = env('TWILIO_ID', false);
        $token = env('TWILIO_TOKEN', false);
        if ( !$sid || !$token || !$fromNumber || empty( $toUsers ) ) { return; }

        $client = new Client( $sid, $token );
        
        foreach( $toPhoneNumbers as $phoneNumber ){
            if ( !$phoneNumber ) { return; }
            try {
                $client->messages
                    ->create(
                        $phoneNumber,
                        array(
                            "from" => "+1".$fromNumber,
                            "body" => $message,
                        )
                );
            } catch ( Exception $e ) {
                
            }
        }

    }

    private static function getPhoneNumbers ( $to ) {
        $type = gettype( $to );
        $return = false;
        if ( $type == 'string' ) {
            $return = $to;
        } elseif ( $to instanceof User ) {
            $return = $to->phone_number;
        } elseif ( $type == 'integer' ) {
            $user = User::find( $to );
            if ( $user ) {
                $return = $user->phone_number;
            }
        }
        if ( $return ) { $return = '+1'.$return; }
        return $return;
    }

}