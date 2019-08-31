<?php


namespace App\Models;


class LastLogin extends BaseModel
{
    protected $fillable = [
        'user_id', 'last_login_at', 'last_login_ip'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'id', 'user_id'
    ];
}
