<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'hired_worker_id', 'title', 'description', 'duration', 'frequency', 'originating_amount', 'terminating_amount',
        'supporting_images', 'address', 'city', 'state', 'latitude', 'longitude', 'is_submitted', 'is_approved', 'is_running', 'is_completed'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function employer()
    {
        return $this->hasOne(User::class, 'employer_id', 'id');
    }

    public function hiredWorker()
    {
        return $this->hasOne(User::class, 'hired_worker_id', 'id')->with('profile')->select([
           'id', 'email', 'phone', 'profile.first_name', 'profile.last_name'
        ]);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'employer_id', 'user_id');
    }
}
