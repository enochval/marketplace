<?php


namespace App\Models;


class Category extends BaseModel
{
    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
