<?php


namespace App\Repositories\Contracts;
interface IJobRepository
{
  public function getJobs();

  public function postJob(int $employer_id, array $params);

  public function getSingleJob($param);
}
