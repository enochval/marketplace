<?php


namespace App\Models;


class JobPitch extends BaseModel
{
    protected $fillable = [
        'job_board_id', 'worker_id', 'amount', 'proposal', 'is_hired'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $appends = [
        'job_title'
    ];

    public function getJobTitleAttribute($value)
    {
        return JobBoard::find($this->attributes['job_board_id'])->title;
    }

    public function worker()
    {
        return $this->hasOne(User::class, 'id', 'worker_id')
            ->select(['id', 'email', 'phone']);
    }

    public function job()
    {
        return $this->hasOne(JobBoard::class, 'id','job_board_id');
    }
}
