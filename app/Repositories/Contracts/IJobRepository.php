<?php


namespace App\Repositories\Contracts;


interface IJobRepository
{
  public function getJobs();

  public function postJob($params);

  public function getSingleJob();
}
