<?php

namespace App\Jobs;

use App\Repositories\Concretes\UserRepository;
use Exception;

class UpdateLastLoginJob extends Job
{
    protected $user_id;
    protected $ip_address;
    /**
     * Create a new job instance.
     *
     * @param $user_id
     * @param $ip_address
     */
    public function __construct($user_id, $ip_address)
    {
        $this->user_id = $user_id;
        $this->ip_address = $ip_address;
    }

    /**
     * Execute the job.
     *
     * @param WorkerRepository $workerRepository
     * @return void
     */
    public function handle(UserRepository $userRepository)
    {
        try {
            $userRepository->updateLastLogin($this->user_id, $this->ip_address);
        } catch (Exception $e) {
            // Log some errors here
        }
    }
}
