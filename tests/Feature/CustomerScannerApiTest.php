<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ServicePoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerScannerApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private ServicePoint $servicePoint;
    private MenuCategory $category;
    private MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Customer QR Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $this->servicePoint = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'QR-001',
            'qr_identifier' => 'customer-qr-001',
            'name' => 'Garden Table',
            'seats' => 4,
            'category' => 'Garden',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->category = MenuCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Starters',
            'code' => 'STA',
            'active' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->menuItem = MenuItem::create([
            'business_id' => $this->business->id,
            'menu_category_id' => $this->category->id,
            'name' => 'Customer Paneer Tikka',
            'description' => 'Smoky paneer starter',
            'category' => 'Starters',
            'type' => 'veg',
            'price' => 180,
            'tax_rate' => 5,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);
    }

    public function test_customer_can_open_scanner_menu_without_token(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Other QR Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        MenuItem::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Business Burger',
            'category' => 'Starters',
            'type' => 'veg',
            'price' => 99,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/customer/scanner/customer-qr-001/menu');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.business.name', 'Customer QR Cafe')
            ->assertJsonPath('data.context.type', 'service_point')
            ->assertJsonFragment(['name' => 'Starters'])
            ->assertJsonFragment(['name' => 'Customer Paneer Tikka'])
            ->assertJsonMissing(['name' => 'Other Business Burger']);
    }

    public function test_customer_can_get_current_coupons_without_token(): void
    {
        Coupon::create([
            'business_id' => $this->business->id,
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order' => 100,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);

        Coupon::create([
            'business_id' => $this->business->id,
            'code' => 'EXPIRED',
            'type' => 'flat',
            'value' => 20,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/customer/scanner/customer-qr-001/coupons');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.context.id', $this->servicePoint->id)
            ->assertJsonFragment(['code' => 'WELCOME10'])
            ->assertJsonMissing(['code' => 'EXPIRED']);
    }

    public function test_customer_can_create_order_from_scanner_without_token(): void
    {
        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Walk In Guest',
            'customer_phone' => '9999999999',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                    'special_instructions' => 'Less spicy',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.context.id', $this->servicePoint->id)
            ->assertJsonPath('data.order.customer_name', 'Walk In Guest')
            ->assertJsonPath('data.order.service_point_id', $this->servicePoint->id);

        $this->assertDatabaseHas('orders', [
            'business_id' => $this->business->id,
            'service_point_id' => $this->servicePoint->id,
            'customer_name' => 'Walk In Guest',
            'customer_phone' => '9999999999',
            'order_status' => 'preparing',
        ]);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem->id,
            'item_name' => 'Customer Paneer Tikka',
            'quantity' => 2,
            'special_instructions' => 'Less spicy',
        ]);
    }
}
