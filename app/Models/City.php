<?php


namespace App\Models;


class City extends BaseModel
{
    protected $fillable = ['name'];

    protected $hidden = [
        'updated_at', 'created_at'
    ];
}
