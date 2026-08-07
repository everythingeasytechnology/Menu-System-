<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Breads', 'code' => 'BRD', 'active' => true],
            ['name' => 'Rice', 'code' => 'RIC', 'active' => true],
            ['name' => 'Veg Main Course', 'code' => 'VMC', 'active' => true],
            ['name' => 'Non-Veg Main Course', 'code' => 'NMC', 'active' => true],
            ['name' => 'Tandoori Special', 'code' => 'TSP', 'active' => true],
            ['name' => 'Rolls', 'code' => 'ROL', 'active' => true],
            ['name' => 'Starters', 'code' => 'STR', 'active' => true],
            ['name' => 'Momos', 'code' => 'MOM', 'active' => true],
            ['name' => 'Pakode', 'code' => 'PAK', 'active' => true],
            ['name' => 'Beverages', 'code' => 'BEV', 'active' => true],
        ];

        foreach ($categories as $cat) {
            MenuCategory::create($cat);
        }
    }
}
