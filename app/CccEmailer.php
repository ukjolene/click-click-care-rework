<?php

namespace App;

use App\User;
use Mail;

class CccEmailer {

    private static function isAssociative ( $array ) {
        foreach ( array_keys( $array ) as $key ) {
            if ( !is_int( $key ) ) {
                return true;
            }
        }
        return false;
    }

    private static function getUserGroupEmails ( $userGroupInput ) {
        $userGroupEmails = array();
        if ( gettype( $userGroupInput ) == 'string' ) {
            $userGroupEmails[] = $userGroupInput;
        } elseif ( $userGroupInput instanceof User ) {
            $userGroupEmails[] = $userGroupInput->email;
        } elseif ( gettype( $userGroupInput ) == 'integer' ) {
            $user = User::find( $userGroupInput );
            if ( $user ) {
                $userGroupEmails[] = $user->email;
            }
        } elseif ( is_array( $userGroupInput ) ) {
            foreach ( $userGroupInput as $toUser ) {
                if ( gettype( $toUser ) == 'string' ) {
                    $userGroupEmails[] = $toUser;
                } elseif ( $toUser instanceof User ) {
                    $userGroupEmails[] = $toUser->email;
                } elseif ( gettype( $toUser ) == 'integer' ) {
                    $user = User::find( $toUser );
                    if ( $user ) {
                        $userGroupEmails[] = $user->email;
                    }
                }
            }
        }
        return $userGroupEmails;
    }

    private static function getEmailsList ( $toInput ) {
        $toEmails = array();
        $ccEmails = array();
        $bccEmails = array();
        if ( gettype( $toInput ) == 'string' ) {
            $toEmails[] = $toInput;
        } elseif ( $toInput instanceof User ) {
            $toEmails[] = $toInput->email;
        } elseif ( gettype( $toInput ) == 'integer' ) {
            $user = User::find( $toInput );
            if ( $user ) {
                $toEmails[] = $user->email;
            }
        } elseif ( is_array( $toInput ) ) {
            if ( !self::isAssociative( $toInput ) ) {
                $toEmails = self::getUserGroupEmails( $toInput );
            } else {
                if ( array_key_exists( 'to', $toInput ) ) {
                    $toEmails = self::getUserGroupEmails( $toInput['to'] );
                }
                if ( array_key_exists( 'cc', $toInput ) ) {
                    $ccEmails = self::getUserGroupEmails( $toInput['cc'] );
                }
                if ( array_key_exists( 'bcc', $toInput ) ) {
                    $bccEmails = self::getUserGroupEmails( $toInput['bcc'] );
                }
            }
        }

        $toEmails = array_filter( $toEmails, function ( $val ) { return filter_var( $val, FILTER_VALIDATE_EMAIL ); } );
        $ccEmails = array_filter( $ccEmails, function ( $val ) { return filter_var( $val, FILTER_VALIDATE_EMAIL ); } );
        $bccEmails = array_filter( $bccEmails, function ( $val ) { return filter_var( $val, FILTER_VALIDATE_EMAIL ); } );

        return (object)array(
            'to' => $toEmails,
            'cc' => $ccEmails,
            'bcc' => $bccEmails
        );
    }

    public static function sendPublicEmail ( $toInput, $subject, $messageText ) {
        $toEmails = self::getEmailsList( $toInput );
        if ( empty( $toEmails->to ) ) { return false; }
        self::sendEmail( $toEmails, $subject, $messageText );
        return true;
    }

    public static function sendPrivateEmail ( $toInput, $subject, $messageText ) {
        $toEmails = self::getEmailsList( $toInput );
        if ( empty( $toEmails->to ) ) { return false; }
        foreach ( $toEmails->to as $toEmail ) {
            $sendToEmails = clone $toEmails;
            $sendToEmails->to = $toEmail;
            self::sendEmail( $sendToEmails, $subject, $messageText );
        }
        return true;
    }

    private static function sendEmail ( $toEmails, $subject, $messageText ) {
        $fromAddress = env('MAIL_FROM_ADDRESS');
        $fromName = env('MAIL_FROM_NAME');
        Mail::send('emails.massRecipientEmailTemplate', ['bodymessage'=> $messageText],
            function( $message ) use ( $fromAddress, $fromName, $toEmails, $subject ) {
                $message->from( $fromAddress, $fromName );
                $message->to( $toEmails->to )
                        ->cc( $toEmails->cc )
                        ->bcc( $toEmails->bcc )
                        ->subject( $subject );

                $sendgridtemplate = ["filters"=>
                                        ["templates"=>[
                                            "settings"=>[
                                                "enable" => '1',
                                                "template_id" => 'e2c9ece4-6cba-497e-b092-53e0e70c755d'
                                            ]
                                        ]]];
                $messageHeader = $message->getHeaders();
                $messageHeader->addTextHeader( 'X-SMTPAPI', json_encode( $sendgridtemplate ) );
            }
        );
    }

}