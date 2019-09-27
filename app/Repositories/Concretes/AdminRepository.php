<?php


namespace App\Repositories\Concretes;

use App\Models\Category;
use App\Models\City;

class AdminRepository
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
}
