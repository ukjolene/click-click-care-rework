<?php

namespace App\Api\V1\Controllers;

use App\Message;
use JWTAuth;
use App\User;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoadMessageRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Load a message
 *
 * @Resource("Message")
 */

class LoadMessageController extends Controller
{
    /**
     *
     * Get Message
     *
     * Get Message using the id
     *
     * @Post("/api/message/load")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer{token}"},body={"id": "message_id"})
     * @Response(201, body={"array": "message"})
     *
     */
    public function load(LoadMessageRequest $request){

        $currentUser = JWTAuth::parseToken()->authenticate();
        switch ($request->get('status')){
            case 'received':
                $message = $currentUser->messagesReceived()
                                        ->with([
                                            'sender' => function($query){
                                                $query->select('id', 'first_name', 'last_name', 'email');
                                            },
                                            'recipient' => function($query){
                                                $query->select('id', 'first_name', 'last_name', 'email');
                                            }
                                        ])
                                        ->where('id', $request->get('id'))
                                        ->first();
                if(!$message->read){
                    $message->read = '1';
                    $message->update();
                }
                break;
            case 'sent':
                $message = $currentUser->messagesSent()
                                        ->with([
                                            'sender' => function($query){
                                                $query->select('id', 'first_name', 'last_name', 'email');
                                            },
                                            'recipient' => function($query){
                                                $query->select('id', 'first_name', 'last_name', 'email');
                                            }
                                        ])
                                        ->where('id', $request->get('id'))
                                        ->first();
                break;
        }
        if($message->reply_to_id){
            $message->reply_to = $this->findReply($message);
        }


        // $message->sender = User::find($message->sender_id);
        // $message->recipient = User::find($message->recipient_id);

        // $message->sender->name = $this->loadUserName(User::find($message->sender_id));
        // $message->recipient->name = $this->loadUserName(User::find($message->recipient_id));
        return response()->json([
            'status' => 'ok',
            'data' => $message
        ], 201);
    }

    private function findReply($message){
        $myreply = Message::withTrashed()->with([
                                                'sender' => function($query){
                                                    $query->select('id', 'first_name', 'last_name', 'email');
                                                }
                                            ])
                                            ->where('id', $message->reply_to_id)
                                            ->get()
                                            ->first();
        if($myreply->reply_to_id){
            $myreply->reply_to = $this->findReply($myreply);
        }
        return $myreply;
    }

}
