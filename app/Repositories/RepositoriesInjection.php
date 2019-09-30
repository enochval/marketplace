<?php


namespace App\Repositories;

use App\Repositories\Concretes\AdminRepository;
use App\Repositories\Contracts\IAdminRepository;
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
            IUserRepository::class,
            UserRepository::class
        );

        $this->app->bind(
            IJobRepository::class,
            JobRepository::class
        );

        $this->app->bind(
            IAdminRepository::class,
            AdminRepository::class
        );
    }
}
