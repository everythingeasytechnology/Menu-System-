<?php

namespace Database\Seeders;

use App\Models\PresetFoodImage;
use Illuminate\Database\Seeder;

class PresetFoodImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presets = [
            [
                'name' => 'Gourmet Ribeye Steak',
                'tags' => 'steak, ribeye, beef, meat, non-veg, grilled',
                'image_path' => 'images/defaults/steak.jpg',
            ],
            [
                'name' => 'Fresh Caesar Salad',
                'tags' => 'salad, caesar, veg, green, healthy, diet',
                'image_path' => 'images/defaults/salad.jpg',
            ],
            [
                'name' => 'Steaming Hot Momos',
                'tags' => 'momos, dimsum, steam, chicken, non-veg, dumplings',
                'image_path' => 'images/defaults/momos.jpg',
            ],
            [
                'name' => 'Double Cappuccino Coffee',
                'tags' => 'coffee, cappuccino, latte, hot, beverage, drink',
                'image_path' => 'images/defaults/coffee.jpg',
            ],
            [
                'name' => 'Pepperoni Wood Pizza',
                'tags' => 'pizza, pepperoni, cheese, fastfood, junkfood, hot',
                'image_path' => 'images/defaults/pizza.jpg',
            ],
            // Map the salad image as a placeholder for Paneer too, so searching "paneer" shows it!
            [
                'name' => 'Paneer Butter Masala Curry',
                'tags' => 'paneer, kadai paneer, curry, gravy, veg main course, indian, veg',
                'image_path' => 'images/defaults/salad.jpg',
            ]
        ];

        foreach ($presets as $preset) {
            PresetFoodImage::create($preset);
        }
    }
}
