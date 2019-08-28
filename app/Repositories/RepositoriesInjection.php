<?php


namespace App\Repositories;

use App\Repositories\Concretes\WorkerRepository;
use App\Repositories\Contracts\IWorkerRepository;
use Illuminate\Support\ServiceProvider;

class RepositoriesInjection extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            IWorkerRepository::class,
            WorkerRepository::class
        );
    }
}
