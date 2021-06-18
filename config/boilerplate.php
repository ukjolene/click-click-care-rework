<?php

return [

    'patient_sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'validation_rules' => [
            'email'         => 'required|email|unique:users',
            'password'      => 'required',
            'first_name'    => 'required',
            'last_name'     => 'required',
            'gender'        => 'required|in:male,female',
            'address'       => 'required',
            'city'          => 'required',
            'province'      => 'required',
            'postal_code'   => ['required', 'regex:/^[ABCEGHJKLMNPRSTVXY][0-9][ABCEGHJKLMNPRSTVWXYZ] ?[0-9][ABCEGHJKLMNPRSTVWXYZ][0-9]$/'],
            'YOB'           => 'required',
            'MOB'           => 'required',
            'DOB'           => 'required',
            'healthcard'    => 'required',
            'cardnumber'    => 'required',
            'cvv'           => 'required',
            'expmonth'      => 'required',
            'expyear'       => 'required'
        ]
    ],

    'provider_sign_up' => [
        'release_token' => env('SIGN_UP_RELEASE_TOKEN'),
        'validation_rules' => [
            'email'             => 'required|email|unique:users',
            'password'          => 'required',
            'first_name'        => 'required',
            'last_name'         => 'required',
            'position_id'       => 'required',
            'gender'            => 'required|in:male,female',
            'address'           => 'required',
            'city'              => 'required',
            'province'          => 'required',
            'postal_code'       => ['required', 'regex:/^[ABCEGHJKLMNPRSTVXY][0-9][ABCEGHJKLMNPRSTVWXYZ] ?[0-9][ABCEGHJKLMNPRSTVWXYZ][0-9]$/'],
            // 'title'             => 'required',
            'position_id'       => 'required',
            'certificate'       => 'required',
            'license_number'    => 'required',
            'language'          => 'required',
            'distance'          => 'required',
            'description'       => 'required',
            'privatepatient'    => 'required|boolean',
            'office_address'    => 'required_if:fOfficeAddressDifferent,true',
            'office_city'       => 'required_if:fOfficeAddressDifferent,true',
            'office_province'   => 'required_if:fOfficeAddressDifferent,true',
            'office_city'       => 'required_if:fOfficeAddressDifferent,true',
            'office_postal_code'=> ['nullable', 'required_if:fOfficeAddressDifferent,true', 'regex:/^[ABCEGHJKLMNPRSTVXY][0-9][ABCEGHJKLMNPRSTVWXYZ] ?[0-9][ABCEGHJKLMNPRSTVWXYZ][0-9]$/']
        ]
    ],

    'login' => [
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    'forgot_password' => [
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    'reset_password' => [
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]
    ],

    'sendMessage' => [
        'validation_rules' => [
            'recipient' => 'required',
            'subject' => 'required',
            'content' => 'required'
        ]
    ],

    'deleteMessage' => [
        'validation_rules' => [
            'id' => 'required'
        ]
    ],

    'loadMessage' => [
        'validation_rules' => [
            'id' => 'required'
        ]
    ],

    'listMessage' => [
        'validation_rules' => [
            'id' => 'required'
        ]
    ],

    'setAvailability' => [
        'validation_rules' => [
            'duration' => 'required_with:shifts|numeric|min:0',
            'days' => 'required|array',
            'days.*' => 'required|date_format:"Y-m-d"',
            'shifts' => 'sometimes|array',
            'shifts.*.start' => 'required|date_format:"H:i"',
            'shifts.*.end' => 'required|date_format:"H:i"',
        ]
    ],

    'blockTimeSlot' => [
        'validation_rules' => [
            'timeslot' => 'required',
            'block' => 'sometimes|boolean'
        ]
    ],

    'filterTimeSlots' => [
        'validation_rules' => [
            'provider' => 'sometimes', // is this autocomplete with ID or submit name as string?
            'position' => 'sometimes',
            'date' => 'required|date_format:"Y-m-d"',
            'language' => 'sometimes',
            'gender' => 'sometimes',
            'health_card' => 'sometimes|boolean'
        ]
    ],

    'bookingDetails' => [
        'validation_rules' => [
            'timeslot' => 'required|numeric|min:1'
        ]
    ],

    'bookAppointment' => [
        'validation_rules' => [
            'timeslot' => 'required|numeric|min:1',
            'address' => 'required',
            'for_someone_else' => 'sometimes'
        ]
    ],

    'cancelAppointment' => [
        'validation_rules' => [
            'appointment' => 'required'
        ]
    ],

    'confirmAppointment' => [
        'validation_rules' => [
            'appointment' => 'required'
        ]
    ],

    'providerSchedule' => [
        'validation_rules' => [
            'date' => 'required|date'
        ]
    ],

    'patientSchedule' => [
        'validation_rules' => []
    ],

    'providerProfile' => [
        'validation_rules' => [
            'id' => 'required|numeric|min:1'
        ]
    ],

    'patientProfile' => [
        'validation_rules' => [
            'id' => 'required|numeric|min:1'
        ]
    ],

];
