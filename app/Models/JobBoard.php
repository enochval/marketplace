<?php


namespace App\Models;


class JobBoard extends BaseModel
{
    protected $fillable = [
        'employer_id', 'worker_id', 'title', 'description', 'duration', 'originating_amount', 'terminating_amount',
        'images', 'address', 'city', 'state', 'latitude', 'longitude', 'is_published', 'is_active', 'is_running'
    ];

    protected $hidden = [
        'updated_at'
    ];
}
