<?php


namespace App\Models;


class AgentCustomer extends BaseModel
{
    protected $fillable = [ 'user_id', 'worker_id' ];

    protected $hidden = [
        'updated_at'
    ];

    public function workers()
    {
        return $this->hasMany(User::class, 'id', 'worker_id')
            ->select(['id', 'email', 'phone'])
            ->with('profile');
    }
}
