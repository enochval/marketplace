<?php


namespace App\Utils;

class Rules
{
    const RULES = [
        'REGISTER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users|email',
            'phone' => 'required|unique:users|numeric',
            'password' => 'required|min:8'
        ]
    ];

    public static function get($rule)
    {
        return data_get(self::RULES, $rule);
    }

}
