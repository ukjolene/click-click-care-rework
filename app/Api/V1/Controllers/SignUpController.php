<?php

namespace App\Api\V1\Controllers;

use Mail;
use Config;
use Storage;
use App\User;
use App\Role;
use App\Value;
use App\Avatar;
use App\Office;
use App\Position;
use App\Language;
use App\Creditcard;
use App\Qualification;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\PatientSignUpRequest;
use App\Api\V1\Requests\ProviderSignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignUpController extends Controller
{

    /**
     *
     * Sign up as a patient
     *
     * Sign up as a patient
     *
     * @Post("/api/auth/signupaspatient")
     * @Versions({"v1"})
     * @Transaction({
     *  @Request(
     *           body={
     *                  "email"         : "string", 
     *                  "password"      : "string", 
     *                  "first_name"    : "string", 
     *                  "last_name"     : "string", 
     *                  "gender"        : "'male' or 'female'", 
     *                  "address"       : "string", 
     *                  "city"          : "string", 
     *                  "province"      : "string", 
     *                  "postal_code"   : "6 digit string", 
     *                  "latitude"      : "coordinate",
     *                  "longitude"     : "coordinate",
     *                  "phone_number"  : "10 digit number (optional)", 
     *                  "YOB"           : "4 digit year of birth",
     *                  "MOB"           : "2 digit month of birth",
     *                  "DOB"           : "2 digit date of birth",
     *                  "healthcard"    : "boolean for yes/no"
     *                }, identifier="Patient")
     * })
     * @Response(201, body={"token": "JWTtoken", "status": "ok"})
     *
     */
    public function signUpAsPatient(PatientSignUpRequest $request, JWTAuth $JWTAuth)
    {
        // Sign up fields
        $lstSignupFields = Config::get('boilerplate.signup.fields');
        $lstSignupData = $request->only($lstSignupFields);

        // Create a new user object
        $user = new User($lstSignupData);

        // Set role to patient
        $objRole = Role::where('role', 'patient')->first();
        $user->role()->associate($objRole);

        if(!$user->save()) {
            throw new HttpException(500);
        }

        // Additional properties
        foreach($objRole->properties as $property) {
            $objValue = new Value();
            $objValue->value = $request->get($property->property);
            if(isset($objValue->value)) {
                $objValue->user()->associate($user);
                $objValue->property()->associate($property);
                $objValue->save();
            }
        }

        // User's credit card details
        $objCrCard = new CreditCard();
        $objCrCard->cardnumber = $request->cardnumber;
        $objCrCard->expmonth = $request->expmonth;
        $objCrCard->expyear = $request->expyear;
        $objCrCard->cvv = $request->cvv;
        $objCrCard->patient()->associate($user);
        $objCrCard->save();


        // Send confirmation email
        $sSenderName = $user->first_name . ' ' . $user->last_name;
        Mail::send('emails.patientregistrationconfirmation',
                   ['username' => $sSenderName, 'email' => $user->email],
                   function($message) use ($sSenderName, $user) {
                       $message->to($user->email, $sSenderName)->subject("Patient Registration Confirmation");
                       $sendgridTemplate = [ 'filters' => [
                                                'templates' => [
                                                    'settings' => [
                                                        'enable' => '1',
                                                        'template_id' => 'd7078c9e-800c-44d4-80e7-90ea80ae211b'
                                                    ]
                                                ]
                                            ]];
                        $msgHeader = $message->getHeaders();
                        $msgHeader->addTextHeader('X-SMTPAPI', json_encode($sendgridTemplate));
                   });

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }

    /**
     *
     * Sign up as a provider
     *
     * Sign up as a provider
     *
     * @Post("/api/auth/signupasprovider")
     * @Versions({"v1"})
     * @Transaction({
     *  @Request(
     *           body={
     *                  "email"         : "string", 
     *                  "password"      : "string", 
     *                  "first_name"    : "string", 
     *                  "last_name"     : "string", 
     *                  "gender"        : "'male' or 'female'", 
     *                  "address"       : "string", 
     *                  "city"          : "string", 
     *                  "province"      : "string", 
     *                  "postal_code"   : "6 digit string", 
     *                  "latitude"      : "coordinate",
     *                  "longitude"     : "coordinate",
     *                  "phone_number"  : "10 digit number (optional)", 
     *                  "title"         : "professional title (e.g. MD)",
     *                  "position_id"   : "position foreign key",
     *                  "certificate"   : "base 64 encoding of file attachment",
     *                  "license_number": "string",
     *                  "distance"      : "number",
     *                  "description"   : "string",
     *                  "privatepatient": "boolean to indicate if accept private patient",
     *                  "avatar"        " "base 64 encoding of image (optional)"
     *                }, identifier="Patient")
     * })
     * @Response(201, body={"token": "JWTtoken", "status": "ok"})
     *
     */
    public function signUpAsProvider(ProviderSignUpRequest $request, JWTAuth $JWTAuth)
    {
        // Validate Certificate File Format
        if($request->has('certificate')) {
            $fileType = $this->getFileType($request->certificate);
            if(!array_key_exists($fileType, $this->acceptableFileTypes)) {
                return response()->json([
                    'status' => 'error', 'message' => 'Incorrect file format provided. Only PDF and DOCX are allowed.'
                ], 200);
            }
        }

        // Sign up fields
        $lstSignupFields = Config::get('boilerplate.signup.fields');
        $lstSignupData = $request->only($lstSignupFields);
        
        $user = new User($lstSignupData);

        // Set role to provider
        $objRole = Role::where('role', 'provider')->first();
        $user->role()->associate($objRole);

        if(!$user->save()) {
            throw new HttpException(500);
        }

        // Additional properties
        foreach($objRole->properties as $property) {
            $objValue = new Value();
            $objValue->value = $request->get($property->property);
            if(isset($objValue->value)) {
                $objValue->user()->associate($user);
                $objValue->property()->associate($property);
                $objValue->save();
            }
        }

        // Save avatar, if image uploaded
        if($request->has('avatar')) {
            $this->saveAvatar($request->avatar, $user);
        }

        // Create a new office address
        $objOfficeAddress = new Office();
        if($request->fOfficeAddressDifferent) {
            $objOfficeAddress->address = $request->office_address;
            $objOfficeAddress->city = $request->office_city;
            $objOfficeAddress->province = $request->office_province;
            $objOfficeAddress->postal_code = $request->office_postal_code;
            $objOfficeAddress->latitude = $request->office_lat;
            $objOfficeAddress->longitude = $request->office_lng;
        } else {
            $objOfficeAddress->address = $request->address;
            $objOfficeAddress->city = $request->city;
            $objOfficeAddress->province = $request->province;
            $objOfficeAddress->postal_code = $request->postal_code;
            $objOfficeAddress->latitude = $request->latitude;
            $objOfficeAddress->longitude = $request->longitude;
        }
        $objOfficeAddress->provider()->associate($user);
        $objOfficeAddress->save();

        // Create new qualification
        $fileExtension = $this->acceptableFileTypes[$this->getFileType($request->certificate)];
        $filename = 'user_qualification_' . $user->id . $fileExtension;
        $folderpath = 'qualifications';

        Storage::put($folderpath . '/' . $filename, base64_decode($request->certificate));
        $objQualification = new Qualification();
        $objQualification->user_id = $user->id;
        $objQualification->position_id = $request->position_id;
        $objQualification->file = $filename;
        $objQualification->save();

        // Send confirmation email
        $sSenderName = $user->first_name . ' ' . $user->last_name;
        Mail::send('emails.providerregistrationconfirmation',
                    ['username' => $sSenderName, 'email' => $user->email],
                    function($message) use ($sSenderName, $user) {
                        $message->to($user->email, $sSenderName)->subject("Provider Registration Confirmation");
                        $sendgridTemplate = [ 'filters' => [
                                                    'templates' => [
                                                        'settings' => [
                                                            'enable' => '1',
                                                            'template_id' => 'd7078c9e-800c-44d4-80e7-90ea80ae211b'
                                                        ]
                                                    ]
                                                ]];
                            $msgHeader = $message->getHeaders();
                            $msgHeader->addTextHeader('X-SMTPAPI', json_encode($sendgridTemplate));
                    });
        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }

    // Function that returns a list of languages
    public function getPredefinedLanguages() {
        
        $lstLanguages = Language::all();
        
        return response()->json([
            'status' => 'ok',
            'data' => $lstLanguages
        ], 200);
    }

    // Function that returns a list of predefined positions
    public function getPredefinedPositions() {
        $lstPositions = Position::all();

        return response()->json([
            'status' => 'ok',
            'data' => $lstPositions
        ], 200);
    }

    // -- List of acceptable formats for file attachments
    // -- -----------------------------------------------
    private $acceptableFileTypes = array(
        "application/pdf" => ".pdf", 
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => ".docx"
    );

    // -- Function to return the type of a base64 file
    // -- --------------------------------------------
    private function getFileType($fileContent) {
        //return $fileContent = substr($fileContent, 5, strpos($fileContent, ';base') - 5);
        $uploadedFile = base64_decode($fileContent);
        $fileInfo = finfo_open();
        $fileType = finfo_buffer($fileInfo, $uploadedFile, FILEINFO_MIME_TYPE);
        return $fileType;
    }

    // -- Function to save a user's avatar image
    // -- --------------------------------------
    private function saveAvatar($avatar64, $user) {
        // parse base64 string : data:image/$type;base64, $imgstr
        $imgArr = explode(',', $avatar64);
        
        // 1, get image type
        $ini = substr($imgArr[0], 11);
        $typeArr = explode(';', $ini);
        $type = $typeArr[0];

        // 2, get image file
        $avatarImg = base64_decode($imgArr[1]);

        // file will be saved @storage/app/avatar
        $filename = 'user_' . $user->id . '_avatar.' . $type;
        $folderpath = "avatar";
        if($user->avatar){
            Storage::delete($folderpath . '/' . $user->avatar);
        }
        else{
            $user->avatar = new Avatar();
        }

        Storage::put($folderpath . '/' . $filename, $avatarImg);

        $user->avatar->user_id = $user->id;
        $user->avatar->avatar = $filename;
        $user->avatar->save();
    }

}
