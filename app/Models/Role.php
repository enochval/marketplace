<?php

namespace App\Models;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    const
        EMPLOYER = 'employer',
        WORKER = 'worker',
        AGENT = 'agent',
        ADMIN = 'admin';

    protected $fillable = [
        'name', 'display_name', 'description'
    ];

    protected $hidden = [
        'id', 'description', 'created_at', 'updated_at', 'pivot'
    ];
}
