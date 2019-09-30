<?php


namespace App\Repositories\Contracts;

interface IUserRepository
{
    public function register(array $params, $role);

    public function workHistory(int $user_id, array $params);

    public function verifyEmail($token);

    public function authenticate(array $credentials);

    public function updateLastLogin($user_id, $ip);

    public function profile(int $user_id, array $params);

    public function bvnVerification(int $user_id, int $bvn = null);

    public function callback(string $reference);

    public function getBvnAnalysis(int $user_id);

    public function updatePassword(int $user_id, array $params);

    public function registerWorkerByAgent(int $user_id, array $params);

    public function getAgentWorkers(int $user_id);

    public function subscribe($user_id, $callback_url);

    public function manualSubscription($user_id);

    public function allUsers($perPage = 15, $orderBy = 'created_at', $sort = 'desc');
}
