<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'title', 'description', 'duration', 'frequency', 'budget', 'category_id', 'gender', 'no_of_resource',
        'address', 'city_id', 'state', 'is_submitted', 'is_approved', 'is_running', 'is_completed', 'hired_count'
    ];

    protected $hidden = [
        'updated_at', 'city_id', 'category_id'
    ];

    public function employer()
    {
        return $this->hasOne(User::class, 'employer_id', 'id')->with('profile');
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id')->select([
            'id', 'name'
        ]);
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select([
            'id', 'name'
        ]);
    }
}
