<?php


namespace App\Repositories\Concretes;

use App\Repositories\Contracts\IWorkerRepository;

class WorkerRepository implements IWorkerRepository
{
    public function register($payload)
    {
        // Persist the user here and update the profile
        // Perform all registration process and return what is necessary to the controller
    }
}
