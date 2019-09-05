<?php

namespace App\Jobs;

use App\Mail\ProfileUpdateEmail;
use Illuminate\Support\Facades\Mail;

class SendProfileUpdateEmailJob extends Job
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
        Mail::to($this->user)->send(new ProfileUpdateEmail($this->user));
    }
}
