<?php


namespace App\Models;

class UsersVerification extends BaseModel
{
    protected $fillable = [
        'user_id', 'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
