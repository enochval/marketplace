<?php


namespace App\Repositories\Concretes;

use App\Models\Category;
use App\Models\City;
use App\Repositories\Contracts\IAdminRepository;

class AdminRepository implements IAdminRepository
{
    public function addCity($params)
    {
        return City::create([
            'name' => $params['city']
        ]);
    }

    public function addCategory($params)
    {
        return Category::create([
            'name' => $params['category']
        ]);
    }

    public function getCities()
    {
        return City::all();
    }

    public function getCategories()
    {
        return Category::all();
    }
}
