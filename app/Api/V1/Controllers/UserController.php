<?php

namespace App\Api\V1\Controllers;

use \Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Cancellation;
use App\Appointment;
use App\Communications;
use JWTAuth;
use DateTime;
use DateTimeZone;
use DateInterval;
use Storage;

class UserController extends Controller {

    public function updateStatus ( Request $request ) {
        //we identify the user using the token
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        $userToUpdate = User::find($request->get('user_id'));
        $userToUpdate->approved = $request->get('status');
        
        if($userToUpdate->save()){
            return response()->json([
                'status' => 'ok'
            ], 201);
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => 'failed to update password'
            ], 201);
        }

    }

    public function getListUsers ( Request $request ) {
        //we identify the user using the token
        $currentUser = JWTAuth::parseToken()->authenticate();
        
        
        $page = ($request->get('page') != "") ? $request->get('page') : 1;
        $request['page'] = "";

        //Retrieve users
        $users = User::where('role_id','!=',1)
        ->with(['role','values.property','avatar']);
        
        //Location
        if($request->has('location')){
            if($request->get('location') != ""){
                $users = $users->whereHas('view',function ($query) use($request) {
                    $query->where('fulladdress', 'like', '%'.$request->get('location').'%');
                });
            }
        }

        //Name or Email
        if($request->has('name')){
            if($request->get('name') != ""){
                $users = $users->whereHas('view',function ($query) use($request) {
                    $query->where('email', 'like', '%'.$request->get('name').'%')
                          ->orWhere('fullname', 'like', '%'.$request->get('name').'%');
                });
            }
        }

        //Status
        if($request->has('status')){
            if($request->get('status') != ""){
                $users = $users->where('approved',$request->get('status'));
            }
        }

        //Role
        if($request->has('role')){
            if($request->get('role') != ""){
                $users = $users->where('role_id',$request->get('role'));
            }
        }

        //Gender
        if($request->has('gender')){
            if($request->get('gender') != ""){
                $users = $users->where('gender',$request->get('gender'));
            }
        }
        
        //Pagination
        $users = $users->paginate(20*$page)->toArray();
        
        //Restructuring the array. It will be easier to access properties
        foreach ($users["data"] as $key => $user) {
            foreach($user["values"] as $value){
                $users["data"][$key][$value["property"]["property"]] = $value["value"];
            }
        }

        //Adding profile picture
        foreach ($users["data"] as $key => $user) {
            if($user["avatar"] != null){
                $users["data"][$key]["avatar64"] = self::getAvatar64AttributeByAvatar($user["avatar"]["avatar"]);
            }
            // else{
            //     $users["data"][$key]["avatar64"] = self::getAvatar64AttributeByAvatar("default.png");
            // }
        }

        //return paginated data
        return response()->json([
            'status' => 'ok',
            'data' => $users
        ], 201);

    }


    public static function getAvatar64AttributeByAvatar ($userAvatar) {
        
        $file = Storage::get("avatar/" . $userAvatar);
        
        $base64str = base64_encode($file);
        $extArr = explode(".", $userAvatar);
        $ext = end($extArr);
        switch ($ext){
            case "jpg":
            case "jpeg":
                $type = "image/jpeg";
                break;
            case "png":
                $type = "image/png";
                break;
            case "gif":
                $type = "image/gif";
                break;
            case "ico":
                $type = "image/x-icon";
                break;
            case "tif":
            case "tiff":
                $type = "image/tif";
                break;
        }
        return array(
            'type' => $type,
            'image' => $base64str
        );
    }
}