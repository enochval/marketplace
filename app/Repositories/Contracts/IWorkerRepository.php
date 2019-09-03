<?php


namespace App\Repositories\Contracts;


interface IWorkerRepository
{
    public function register(array $params);

    public function updateLastLogin($user_id, $ip);
}
