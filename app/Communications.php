<?php
// facade-style interface for all communications

namespace App;

use App\CccEmailer;
use App\CccSmsClient;
use App\Message;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Communications {

    // accepts (string) email, (int) user ID, (User) user, or array of same as $to
    public static function publicEmail ( $to, $subject, $message ) {
        return CccEmailer::sendPublicEmail( $to, $subject, $message );
    }

    // accepts (string) email, (int) user ID, (User) user, or array of same as $to
    public static function privateEmail ( $to, $subject, $message ) {
        return CccEmailer::sendPrivateEmail( $to, $subject, $message );
    }

    // accepts (string) phone number, (int) user ID, (User) user, or array of same as $to
    public static function sendSMS ( $to, $message, $from = false ) {
        return CccSmsClient::sendSMS( $to, $message, $from );
    }

    // ceates a new internal message
    // accepts User model, id, or email for both $sender and $recipient params
    public static function sendMessage ( $recipient, $sender, $subject, $content, $reply_to_id = null ) {
        $sender = self::checkUser( $sender );
        $recipient = self::checkUser( $recipient );
        if ( $recipient->id == $sender->id ) {
            throw new HttpException(503, 'Cannot send message to yourself.');
        }
        $message = new Message( $sender, $recipient, $subject, $content, $reply_to_id );
        $message->send();
    }

    private static function checkUser ( $input ) {
        if ( !$input ) {
            //todo: update default to user by email instead of id?
            $input = User::find(1);
        } elseif ( ! $input instanceof User ) {
            $checkColumn = filter_var( $input, FILTER_VALIDATE_EMAIL ) ? 'email' : 'id';
            $input = User::where( $checkColumn, $input )->get()->first();
        }
        if ( !$input ) {
            throw new HttpException(503, $request->get('recipient').' not found.');
        }
        return $input;
    }

}