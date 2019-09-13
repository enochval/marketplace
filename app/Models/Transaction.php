<?php


namespace App\Models;


class Transaction extends BaseModel
{
    protected $fillable = [
        'user_id', 'reference', 'amount', 'status', 'meta'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
