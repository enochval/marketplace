<?php


namespace App\Repositories\Contracts;

interface IUserRepository
{
  public function verifyEmail($token);

  public function authenticate($credentials);

  public function updateLastLogin($user_id, $ip);
}