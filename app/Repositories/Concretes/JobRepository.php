<?php

use App\Model\JobBoard;

namespace App\Repositories\Concretes;

class JobRepository implements IJobRepository
{
  public function getJobs()
  {
    $jobs = JobBoard::orderBy('id', 'desc')->paginate(10);
    return [
      'payload' => $jobs
    ];
  }

  public function postJob($params)
  {

  }

  public function getSingleJob()
  {

  }
}
