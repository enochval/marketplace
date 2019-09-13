<?php


namespace App\Repositories;

use App\Repositories\Concretes\WorkerRepository;
use App\Repositories\Contracts\IWorkerRepository;
use App\Repositories\Concretes\EmployerRepository;
use App\Repositories\Contracts\IEmployerRepository;
use App\Repositories\Concretes\UserRepository;
use App\Repositories\Contracts\IUserRepository;
use App\Repositories\Concretes\JobRepository;
use App\Repositories\Contracts\IJobRepository;
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

        $this->app->bind(
            IUserRepository::class,
            UserRepository::class
        );

        $this->app->bind(
            IJobRepository::class,
            JobRepository::class
        );
    }
}