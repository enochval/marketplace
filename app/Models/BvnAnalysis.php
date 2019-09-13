<?php


namespace App\Models;


class BvnAnalysis extends BaseModel
{
    protected $fillable = [
        'user_id', 'first_name_match', 'last_name_match', 'dob_match', 'phone_match', 'score'
    ];

    protected $hidden = [
        'updated_at', 'id', 'user_id'
    ];
}
