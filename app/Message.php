<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Symfony\Component\HttpKernel\Exception\HttpException;

class Message extends Model
{
    //    
    use SoftDeletes;
    protected $table = "messages";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'recipient_id', 'reply_to_id', 'subject', 'content', 'read'
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at', 'updated_at'
    ];


    // hacky php polymorphic constructor
    function __construct () {
        $args = func_get_args();
        $numArgs = func_num_args();
        if ( !in_array( $numArgs, array( 0, 1, 4, 5 ) ) ) {
            throw new HttpException( 500, 'Message constructor expects exactly 0, 1, 4 or 5 parameters' );
        }
        $eloquentAtts = ( $numArgs === 1 ) ? $args[0] : array();
        parent::__construct( $eloquentAtts );
        if ( in_array( $numArgs, array( 4, 5 ) ) ) {
            $reply_to_id = ( $numArgs === 5 ) ? $args[4] : '0';
            self::customConstructor( $args[0], $args[1], $args[2], $args[3], $reply_to_id );
        }
    }

    public function customConstructor( $sender, $recipient, $subject, $content, $reply_to_id ) {
        $reply_to_id = ( $reply_to_id === null ) ? '0' : $reply_to_id;
        $this->sender()->associate($sender);
        $this->recipient()->associate($recipient);
        $this->reply_to_id = $reply_to_id;
        $this->subject = $subject;
        $this->content = $content;
        $this->read = '0';
    }

    public function send () {
        if( !$this->save() ) {
            throw new HttpException(500);
        }
    }

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo('App\User', 'recipient_id');
    }

}
