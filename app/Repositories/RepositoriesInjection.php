<?php


namespace App\Repositories;

use App\Repositories\Concretes\WorkerRepository;
use App\Repositories\Contracts\IWorkerRepository;
use App\Repositories\Concretes\EmployerRepository;
use App\Repositories\Contracts\IEmployerRepository;
use Illuminate\Support\ServiceProvider;

class RepositoriesInjection extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            IWorkerRepository::class,
            WorkerRepository::class
        );

        $this->app->bind(
            IEmployerRepository::class,
            EmployerRepository::class
        );
    }
}
