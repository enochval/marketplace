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
        return $this->hasOne(User::class, 'worker_id', 'id')->with('profile');
    }

    public function jobBoard()
    {
        return $this->hasOne(JobBoard::class, 'job_board_id');
    }
}
