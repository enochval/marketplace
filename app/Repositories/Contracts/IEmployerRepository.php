<?php


namespace App\Repositories\Contracts;


interface IEmployerRepository
{
  public function register($params);

  public function updateProfile($params);
}
