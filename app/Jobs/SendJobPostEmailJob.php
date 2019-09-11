<?php

namespace App\Jobs;

use App\Mail\JobPostEmail;
use Illuminate\Support\Facades\Mail;

class SendJobPostEmailJob extends Job
{
  private $user;

  /**
   * Create a new job instance.
   *
   * @param $user
   */
  public function __construct($user)
  {
    $this->user = $user;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    Mail::to($this->user)->send(new JobPostEmail($this->user));
  }
}
