<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Dry Aged Ribeye Steak',
                'category' => 'Veg Main Course',
                'type' => 'non-veg',
                'cooking_time' => '20-25 mins',
                'image_path' => 'images/defaults/steak.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => 'Standard (300g)', 'price' => 42.00],
                    ['label' => 'Large (450g)', 'price' => 58.00],
                ],
            ],
            [
                'name' => 'Paneer Butter Masala',
                'category' => 'Veg Main Course',
                'type' => 'veg',
                'cooking_time' => '15-20 mins',
                'image_path' => 'images/defaults/salad.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => 'Half Portion', 'price' => 160.00],
                    ['label' => 'Full Portion', 'price' => 280.00],
                ],
            ],
            [
                'name' => 'Garlic Naan',
                'category' => 'Breads',
                'type' => 'veg',
                'cooking_time' => '5-7 mins',
                'image_path' => 'images/defaults/pizza.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => 'Single Piece', 'price' => 60.00],
                ],
            ],
            [
                'name' => 'Butter Chicken Special',
                'category' => 'Non-Veg Main Course',
                'type' => 'non-veg',
                'cooking_time' => '20-25 mins',
                'image_path' => 'images/defaults/steak.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => 'Half Portion', 'price' => 240.00],
                    ['label' => 'Full Portion', 'price' => 420.00],
                ],
            ],
            [
                'name' => 'Pan-Seared Atlantic Salmon',
                'category' => 'Non-Veg Main Course',
                'type' => 'non-veg',
                'cooking_time' => '15-20 mins',
                'image_path' => 'images/defaults/steak.jpg',
                'stock' => false,
                'variants' => [
                    ['label' => 'Regular Fillet', 'price' => 34.50],
                ],
            ],
            [
                'name' => 'Double Shot Cappuccino',
                'category' => 'Beverages',
                'type' => 'veg',
                'cooking_time' => '5 mins',
                'image_path' => 'images/defaults/coffee.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => 'Regular Cup', 'price' => 5.50],
                    ['label' => 'Tall Mug', 'price' => 7.50],
                ],
            ],
            [
                'name' => 'Steam Momos Veg',
                'category' => 'Momos',
                'type' => 'veg',
                'cooking_time' => '10 mins',
                'image_path' => 'images/defaults/momos.jpg',
                'stock' => true,
                'variants' => [
                    ['label' => '6 Pcs', 'price' => 90.00],
                    ['label' => '10 Pcs', 'price' => 140.00],
                ],
            ]
        ];

        foreach ($items as $itemData) {
            $variants = $itemData['variants'];
            unset($itemData['variants']);

            if (isset($itemData['image_path'])) {
                $preset = \App\Models\PresetFoodImage::where('image_path', $itemData['image_path'])->first();
                if ($preset) {
                    $itemData['preset_food_image_id'] = $preset->id;
                }
                unset($itemData['image_path']);
            }

            $menuItem = MenuItem::create($itemData);

            foreach ($variants as $variant) {
                $menuItem->variants()->create($variant);
            }
        }
    }
}
