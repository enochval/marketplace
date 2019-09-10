<?php


namespace App\Repositories\Contracts;


interface IWorkerRepository
{
    public function register(array $params);

    public function updateLastLogin($user_id, $ip);

    public function workHistory(int $user_id, array $params);

    public function workerSkills(int $user_id, array $params);
}
