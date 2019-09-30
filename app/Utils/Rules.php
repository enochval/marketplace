<?php


namespace App\Utils;

class Rules
{
    const RULES = [
        'REGISTER_USER' => [
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
            'city_id' => 'required|numeric',
            'state' => 'required|string',
            'bio' => 'required|string',
            'job_interest' => 'sometimes|array',
            'job_interest.*' => 'required|numeric'
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

        'BVN_VERIFICATION' => [
            'bvn' => 'required|digits:11'
        ],

        'SUBSCRIBE' => [
            'callback_url' => 'required|url'
        ],

        'CREATE_JOB' => [
            'title' => 'required',
            'description' => 'required',
            'duration' => 'required|numeric',
            'frequency' => 'required',
            'budget' => 'sometimes',
            'no_of_resource' => 'required',
            'address' => 'required',
            'city_id' => 'required',
            'state' => 'required',
            'category_id' => 'required',
            'gender' => 'sometimes'
        ],

        'BID' => [
            'amount' => 'required|numeric',
            'proposal' => 'sometimes|string'
        ],

        'JOB_REVIEW' => [
            'no_of_stars' => 'required|numeric|max:5',
            'remark' => 'required|nullable'
        ],

        'REGISTER_WORKER_BY_AGENT' => [
            'email' => 'required|unique:users|email',
            'phone' => 'required|unique:users|digits:11',
            'password' => 'required|confirmed|min:8',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'avatar' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'bio' => 'required|string',
            'job_interest' => 'required|array',
            'bvn' => 'required|digits:11',
            'employer' => 'required|string',
            'position' => 'required|string',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ]
    ];

    public static function get($rule, $validation = [])
    {
        $rules = data_get(self::RULES, $rule);
        if ($validation) {
            foreach ($validation as $key => $item) {
                data_set($rules, $key, $item, true);
            }
        }
        return $rules;
    }

}
