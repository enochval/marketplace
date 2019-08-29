<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable;
    use EntrustUserTrait;
//    use Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone', 'email', 'password', 'is_active', 'is_premium', 'is_confirmed', 'is_ban', 'is_verified', 'is_bvn_verified', 'profile_updated'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'updated_at'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
