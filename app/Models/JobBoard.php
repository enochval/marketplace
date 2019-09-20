<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'title', 'description', 'duration', 'frequency', 'amount', 'no_of_resource', 'supporting_images',
        'address', 'city', 'state', 'latitude', 'longitude', 'is_submitted', 'is_approved', 'is_running', 'is_completed',
        'hired_count'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function getSupportingImagesAttribute()
    {
        return json_decode($this->attributes['supporting_images'], true);
    }

    public function employer()
    {
        return $this->hasOne(User::class, 'employer_id', 'id')->with('profile');
    }
}
