<?php

namespace App\Api\V1\Controllers;

use App\Message;
use JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\V1\Requests\ListMessageRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Retrieve a list of messages
 *
 * @Resource("Message")
 */

class ListMessageController extends Controller
{
	/**
     *
     * List of received messages
     *
     * Return the list of messages for the authenticated user
     *
     * @Post("/api/message/messagesReceived")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"})
     * @Response(201, body={"array": "messages"})
     *
     */
    public function messagesReceived(ListMessageRequest $request){

        $currentUser = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => 'ok',
            'data' => $currentUser->messagesReceived()
                                    ->with([
                                        'sender' => function($query){
                                            $query->select('id', 'first_name', 'last_name');
                                        }
                                    ])
                                    ->orderBy('id','desc')
                                    ->get()
                                    ->toArray()
        ], 201);

    }

    /**
     *
     * List of sent messages
     *
     * Return the list of messages for the authenticated user
     *
     * @Post("/api/message/messagesSent")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"})
     * @Response(201, body={"array": "messages"})
     *
     */
    public function messagesSent(ListMessageRequest $request){

        $currentUser = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => 'ok',
            'data' => $currentUser->messagesSent()
                                    ->with([
                                        'recipient' => function($query){
                                            $query->select('id', 'first_name', 'last_name');
                                        }
                                    ])
                                    ->orderBy('id','desc')
                                    ->get()
                                    ->toArray()
        ], 201);
    }

    /**
     *
     * Show the number of unread message
     *
     * Return the number of unread messages for the authenticated user
     *
     * @Post("/api/message/unreadMessage")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"})
     * @Response(201, body={"array": "messages"})
     *
     */
    public function unreadMessage(){
        $currentUser = JWTAuth::parseToken()->authenticate();

        $unread = sizeof($currentUser->messagesReceived->where('read', '0'));

        return response()->json([
            'status' => 'ok',
            'unread' => $unread
        ], 201);
    }



    /**
     *
     * Mark messages as read
     *
     * Mark messages as read
     *
     * @Post("/api/message/markread")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"})
     * @Response(201)
     *
     */
    public function markAsRead ( Request $request ) {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $messageIDs = $request->get('id');
        if ( gettype( $messageIDs ) != 'array' ) {
            $messageIDs = array( $messageIDs );
        }

        $messages = $currentUser->messagesReceived()->whereIn('id', $messageIDs)->get();

        $markAs = ( $request->get('read') ) ? '1' : '0';

        foreach ( $messages as $message ) {
            $message->read = $markAs;
            if( !$message->save() ) {
                throw new HttpException(500);
            }
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }


}
