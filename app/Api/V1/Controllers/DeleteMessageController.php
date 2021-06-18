<?php

namespace App\Api\V1\Controllers;

use App\Message;
use JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\DeleteMessageRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Delete a message
 *
 * @Resource("Message")
 */

class DeleteMessageController extends Controller
{
    /**
     *
     * Delete Message
     *
     * Delete Message using the id
     *
     * @Post("/api/message/delete")
     * @Versions({"v1"})
     * @Request({"id": "message_id"})
     * @Response(201, body={"status": "ok"})
     *
     */
    public function delete(DeleteMessageRequest $request){

        $currentUser = JWTAuth::parseToken()->authenticate();

        $messageIDs = $request->get('id');
        if ( gettype( $messageIDs ) != 'array' ) {
            $messageIDs = array( $messageIDs );
        }

        $messages = $currentUser->messagesReceived()->whereIn('id', $messageIDs)->get();

        foreach ( $messages as $message ) {
            if( !$message->delete() ) {
                throw new HttpException(500);
            }
        }

        return response()->json([
            'status' => 'ok'
        ], 201);
    }
}
