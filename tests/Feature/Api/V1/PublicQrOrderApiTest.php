<?php

namespace Tests\Feature\Api\V1;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\ServicePoint;

class PublicQrOrderApiTest extends ApiTestCase
{
    public function test_public_qr_menu_and_order_creation_uses_server_side_price(): void
    {
        [$business] = $this->createBusinessUser();

        $table = RestaurantTable::create([
            'business_id' => $business->id,
            'name' => 'Table 1',
            'qr_identifier' => 'tbl_public_test',
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        $category = MenuCategory::create([
            'business_id' => $business->id,
            'name' => 'Mains',
            'code' => 'MAI',
            'active' => true,
            'status' => 'active',
        ]);

        $item = MenuItem::create([
            'business_id' => $business->id,
            'menu_category_id' => $category->id,
            'name' => 'Paneer Bowl',
            'category' => 'Mains',
            'type' => 'veg',
            'price' => 200,
            'tax_rate' => 5,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/public/menu/'.$table->qr_identifier)
            ->assertOk()
            ->assertJsonPath('data.context.type', 'table')
            ->assertJsonPath('data.categories.0.items.0.name', 'Paneer Bowl');

        $response = $this->postJson('/api/v1/public/menu/'.$table->qr_identifier.'/orders', [
            'customer_name' => 'Guest',
            'items' => [
                ['menu_item_id' => $item->id, 'quantity' => 2, 'client_price' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subtotal', 400)
            ->assertJsonPath('data.tax', 20)
            ->assertJsonPath('data.total', 420);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $item->id,
            'item_name' => 'Paneer Bowl',
            'price' => 200,
            'quantity' => 2,
        ]);
    }

    public function test_invalid_or_inactive_qr_is_rejected(): void
    {
        [$business] = $this->createBusinessUser();

        RestaurantTable::create([
            'business_id' => $business->id,
            'name' => 'Inactive Table',
            'qr_identifier' => 'tbl_inactive',
            'capacity' => 2,
            'status' => 'available',
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/public/menu/tbl_inactive')
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_service_point_qr_creates_order_against_service_point(): void
    {
        [$business] = $this->createBusinessUser('service-point@example.com');

        $servicePoint = ServicePoint::create([
            'business_id' => $business->id,
            'code' => 'SP-TEST123',
            'qr_identifier' => 'sp_public_test',
            'name' => 'Villa Deck',
            'seats' => 6,
            'category' => 'Villa',
            'point_type' => 'villa',
            'status' => 'available',
            'is_active' => true,
            'amount' => 0,
            'items' => [],
        ]);

        $category = MenuCategory::create([
            'business_id' => $business->id,
            'name' => 'Snacks',
            'code' => 'SNK',
            'active' => true,
            'status' => 'active',
        ]);

        $item = MenuItem::create([
            'business_id' => $business->id,
            'menu_category_id' => $category->id,
            'name' => 'Masala Fries',
            'category' => 'Snacks',
            'type' => 'veg',
            'price' => 100,
            'tax_rate' => 0,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/public/menu/'.$servicePoint->qr_identifier)
            ->assertOk()
            ->assertJsonPath('data.context.type', 'service_point')
            ->assertJsonPath('data.context.point_type', 'villa');

        $this->postJson('/api/v1/public/menu/'.$servicePoint->qr_identifier.'/orders', [
            'items' => [
                ['menu_item_id' => $item->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.service_point_id', $servicePoint->id)
            ->assertJsonPath('data.total', 100);
    }
}
