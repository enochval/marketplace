<?php

use App\Models\JobBoard;
use App\Jobs\SendJobPostEmailJob;
use App\Repositories\Contracts\IEmployerRepository;


namespace App\Repositories\Concretes;

class JobRepository implements IJobRepository
{

  private $employer;

  public function __construct(IEmployerRepository $employer)
  {
    $this->employer = $employer;
  }

  public function getJobs()
  {
    $jobs = JobBoard::orderBy('id', 'desc')->paginate(10);
    return [
      'payload' => $jobs
    ];
  }

  public function postJob($params)
  {
    try {
          [
            'title' => $title,
            'description' => $description,
            'duraiton' => $duration,
            'frequency' => $frequency,
            'amount' => $amount,
            'images' => $images,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'latitude' => $latitude,
            'longitude' => $longitude
          ] = $params;

      // Persist data
      $job = JobBoard::create([
        'title' => $title,
        'description' => $description,
        'duraiton' => $duration,
        'frequency' => $frequency,
        'amount' => $amount,
        'images' => $images,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'latitude' => $latitude,
        'longitude' => $longitude
      ]);

      // Push this verification email to the queue (Basically sends this email to the registered employer)
      dispatch(new SendJopPostEmailJob($this->getEmployer()));
    } catch(Exception $e) {
        return $this->error($e->getMessage());
    }
  }

  public function getSingleJob($id)
  {
    $job = JobBoard::find($id);
    return [
      'payload' => $job
    ];
  }
}
