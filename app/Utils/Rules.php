<?php


namespace App\Utils;

class Rules
{
    const RULES = [
        'REGISTER_WORKER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'sometimes|unique:users|email',
            'phone' => 'required|unique:users|numeric',
            'password' => 'required|confirmed|min:8'
        ],

        'REGISTER_EMPLOYER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users|email',
            'phone' => 'required|unique:users|numeric',
            'password' => 'required|confirmed|min:8'
        ],

        'CONFIRM_EMAIL' => [
            'token' => 'required|string'
        ],

        'AUTHENTICATE' => [
            'email' => 'required',
            'password' => 'required'
        ],

        'UPDATE_EMPLOYER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'bank_verification_number' => 'numeric',
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'date_of_birth' => 'string',
        ],
    ];

    public static function get($rule)
    {
        return data_get(self::RULES, $rule);
    }

}
