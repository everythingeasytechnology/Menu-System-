<?php

namespace Database\Seeders;

use App\Models\ServicePoint;
use Illuminate\Database\Seeder;

class ServicePointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $points = [
            // Dining Hall
            [
                'code' => 'SP-0001',
                'name' => 'Table 1',
                'seats' => 2,
                'category' => 'Dining Hall',
                'status' => 'available',
                'order_number' => null,
                'amount' => 0.00,
                'items' => [],
            ],
            [
                'code' => 'SP-0002',
                'name' => 'Table 2',
                'seats' => 4,
                'category' => 'Dining Hall',
                'status' => 'occupied',
                'order_number' => '#KFC1252',
                'amount' => 395.00,
                'items' => ['1x Zinger Burger', '1x Garlic Bread'],
            ],
            [
                'code' => 'SP-0003',
                'name' => 'Table 3',
                'seats' => 4,
                'category' => 'Dining Hall',
                'status' => 'available',
                'order_number' => null,
                'amount' => 0.00,
                'items' => [],
            ],

            // 1st Floor Rooms
            [
                'code' => 'SP-0004',
                'name' => 'Room 101',
                'seats' => 2,
                'category' => '1st Floor Rooms',
                'status' => 'available',
                'order_number' => null,
                'amount' => 0.00,
                'items' => [],
            ],
            [
                'code' => 'SP-0005',
                'name' => 'Room 102',
                'seats' => 4,
                'category' => '1st Floor Rooms',
                'status' => 'occupied',
                'order_number' => '#KFC1238',
                'amount' => 740.00,
                'items' => ['2x Seafood Fettuccine', '1x Garlic Bread'],
            ],
        ];

        foreach ($points as $p) {
            ServicePoint::create($p);
        }
    }
}
