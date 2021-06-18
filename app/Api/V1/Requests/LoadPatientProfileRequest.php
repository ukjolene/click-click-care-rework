<?php

namespace App\Api\V1\Requests;

use Config;
use Illuminate\Foundation\Http\FormRequest;

class LoadPatientProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Config::get('boilerplate.patientProfile.validation_rules');
    }
}
