<?php


namespace App\Models;


class JobReview extends BaseModel
{
    protected $fillable = [
        'reviewer_id', 'reviewee_id', 'job_id', 'no_of_stars', 'remark'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function Reviewer()
    {
        return $this->hasOne(User::class, 'id', 'reviewer_id')
            ->select(['id', 'email', 'phone'])
            ->with('profile');
    }

    public function Reviewee()
    {
        return $this->hasOne(User::class, 'id', 'reviewee_id')
            ->select(['id', 'email', 'phone'])
            ->with('profile');
    }
}
