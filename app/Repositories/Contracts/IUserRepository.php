<?php


namespace App\Repositories\Contracts;

interface IUserRepository
{
    public function verifyEmail($token);

    public function authenticate(array $credentials);

    public function updateLastLogin($user_id, $ip);

    public function profile(int $user_id, array $params);

    public function bvnVerification(int $user_id, int $bvn);
}
