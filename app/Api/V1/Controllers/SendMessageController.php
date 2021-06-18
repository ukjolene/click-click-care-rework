<?php

namespace App\Api\V1\Controllers;

use App\Communications;
use JWTAuth;
use App\User;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SendMessageRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Send/Reply a message
 *
 * @Resource("Message")
 */


class SendMessageController extends Controller
{
    /**
     * Send message
     *
     * Send message to the user
     *
     * @Post("/api/message/send")
     * @Versions({"v1"})
     * @Transaction({
     *  @Request(body={"recipient_id": "user_id", "reply_to_id": "0",
                         "content": "string"}, identifier="Send Message"),
     *  @Request(body={"recipient_id": "user_id", "reply_to_id": "message_id",
                         "content": "string"}, identifier="Reply Message")
     * })
     * @Response(201, body={"status": "ok"})
     *
     */
    public function send(SendMessageRequest $request){

        $currentUser = JWTAuth::parseToken()->authenticate();

        $reply_to_id = $request->get('reply_to_id');
        $reply_to_id = isset($reply_to_id) ? $reply_to_id : null;

        Communications::sendMessage( $request->get('recipient'), $currentUser, $request->get('subject'), $request->get('content'), $reply_to_id );

        return response()->json([
            'status' => 'ok'
        ], 201);
    }
}
