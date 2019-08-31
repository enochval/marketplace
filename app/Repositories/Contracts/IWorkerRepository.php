<?php


namespace App\Repositories\Contracts;


interface IWorkerRepository
{
    public function register($params);

    public function verifyEmail($token);

    public function authenticate($credentials);

    public function updateLastLogin($user_id, $ip);
}
