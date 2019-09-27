<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'title', 'description', 'duration', 'frequency', 'budget', 'category_id', 'gender', 'no_of_resource',
        'address', 'city_id', 'state', 'is_submitted', 'is_approved', 'is_running', 'is_completed', 'hired_count'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function employer()
    {
        return $this->hasOne(User::class, 'employer_id', 'id')->with('profile');
    }

    public function city()
    {
        return $this->hasOne(City::class);
    }

    public function category()
    {
        return $this->hasOne(Category::class);
    }

    public function hireCheck()
    {
        return $this->hasMany(JobPitch::class, 'job_board_id', 'id')
            ->select(['id', 'is_hired'])
            ->where('worker_id', auth()->id());
    }
}
