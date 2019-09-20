<?php


namespace App\Models;


class GeneralSetting extends BaseModel
{
    protected $fillable = [
        'verification_fee', 'no_of_employer_free_resource', 'no_of_worker_free_trial'
    ];
}
