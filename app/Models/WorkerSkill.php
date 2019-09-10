<?php


namespace App\Models;


class WorkerSkill extends BaseModel
{
    protected $fillable = [
        'user_id', 'names', 'category_id'
    ];

    protected $hidden = [
        'id', 'updated_at'
    ];
}
