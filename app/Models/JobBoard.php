<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'title', 'description', 'duration', 'frequency', 'amount', 'number_of_resource', 'supporting_images',
        'address', 'city', 'state', 'latitude', 'longitude', 'is_submitted', 'is_approved', 'is_running', 'is_completed'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function employer()
    {
        return $this->hasOne(User::class, 'employer_id', 'id')->with('profile');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'employer_id', 'user_id');
    }
}
