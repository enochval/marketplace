<?php


namespace App\Repositories\Contracts;
interface IJobRepository
{
  public function getJobs();

  public function createJob(int $employer_id, array $params);

  public function getSingleJob($param);
}
