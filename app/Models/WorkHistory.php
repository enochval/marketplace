<?php


namespace App\Models;


class WorkHistory extends BaseModel
{
    protected $fillable = [
        'user_id', 'employer', 'position', 'start_date', 'end_date'
    ];

    protected $hidden = [
        'id', 'updated_at', 'created_at'
    ];
}
