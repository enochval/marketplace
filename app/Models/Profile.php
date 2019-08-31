<?php


namespace App\Models;


class Profile extends BaseModel
{
    protected $fillable = [
        'user_id', 'first_name', 'middle_name', 'last_name', 'avatar', 'gender', 'bank_verification_number',
        'date_of_birth', 'address', 'city', 'state', 'latitude', 'longitude'
    ];

    protected $hidden = [
        'user_id', 'id', 'created_at', 'updated_at', 'deleted_at'
    ];
}
