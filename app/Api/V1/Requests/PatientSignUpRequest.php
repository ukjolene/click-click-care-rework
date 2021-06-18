<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class PatientSignUpRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.patient_sign_up.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
