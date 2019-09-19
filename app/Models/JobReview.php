<?php


namespace App\Models;


class JobReview extends BaseModel
{
    protected $fillable = [
        'employer_id', 'worker_id', 'job_id', 'no_of_stars', 'remark'
    ];

    protected $hidden = [
        'updated_at'
    ];
}
