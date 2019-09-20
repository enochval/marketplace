<?php


namespace App\Models;


class JobPitch extends BaseModel
{
    protected $fillable = [
        'job_board_id', 'worker_id', 'amount', 'is_hired'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function worker()
    {
        return $this->hasOne(User::class, 'id', 'worker_id')
            ->select(['id', 'email', 'phone'])
            ->with('profile');
    }

    public function job()
    {
        return $this->hasOne(JobBoard::class, 'id','job_board_id');
    }
}
