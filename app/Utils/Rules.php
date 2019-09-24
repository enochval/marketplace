<?php


namespace App\Utils;

class Rules
{
    const RULES = [
        'REGISTER_WORKER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'sometimes|unique:users|email',
            'phone' => 'required|unique:users|digits:11',
            'password' => 'required|confirmed|min:8'
        ],

        'REGISTER_EMPLOYER' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users|email',
            'phone' => 'required|unique:users|digits:11',
            'password' => 'required|confirmed|min:8'
        ],

        'CONFIRM_EMAIL' => [
            'token' => 'required|string'
        ],

        'AUTHENTICATE' => [
            'email' => 'required',
            'password' => 'required'
        ],

        'UPDATE_PROFILE' => [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'avatar' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'bio' => 'required|string',
        ],

        'CHANGE_PASSWORD' => [
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ],

        'WORK_HISTORY' => [
            'employer' => 'required|string',
            'position' => 'required|string',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ],

        'WORKER_SKILLS' => [
            'names' => 'required|array',
            'category_id' => 'required|numeric'
        ],

        'BVN_VERIFICATION' => [
            'bvn' => 'required|digits:11',
            'callback_url' => 'required|active_url'
        ],

        'CREATE_JOB' => [
            'title' => 'required',
            'description' => 'required',
            'duration' => 'required|numeric',
            'frequency' => 'required',
            'amount' => 'required|numeric',
            'no_of_resource' => 'required',
            'supporting_images' => 'required|array',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required'
        ],

        'BID' => [
            'amount' => 'required|numeric'
        ],

        'JOB_REVIEW' => [
            'no_of_stars' => 'required|numeric|max:5',
            'remark' => 'required|string'
        ]
    ];

    public static function get($rule)
    {
        return data_get(self::RULES, $rule);
    }

}
