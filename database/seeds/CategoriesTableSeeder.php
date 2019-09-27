<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'Warehouse Operations', 'General Labour', 'Delivery Service', 'Food Production', 'Event Staffing',
            'Washing & Cleaning', 'Merchandising Operations', 'Administrative'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category
            ]);
        }
    }
}
