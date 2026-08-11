<?php

namespace Tests\Feature\Api\V1;

use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;

class OrderApiTest extends ApiTestCase
{
    public function test_all_orders_endpoint_returns_business_orders_without_pagination(): void
    {
        [$business, $user] = $this->createBusinessUser('orders-owner@example.com');
        [$otherBusiness] = $this->createBusinessUser('other-orders-owner@example.com');

        $firstOrder = $this->createOrder($business, [
            'order_number' => 'ORD-1001',
            'customer_name' => 'Akhil',
            'order_status' => 'preparing',
            'total' => 250,
        ]);

        $secondOrder = $this->createOrder($business, [
            'order_number' => 'ORD-1002',
            'customer_name' => 'Riya',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'total' => 500,
        ]);

        $this->createOrder($otherBusiness, [
            'order_number' => 'ORD-2001',
            'customer_name' => 'Other Business',
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/orders/all');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'All orders')
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['order_number' => $secondOrder->order_number])
            ->assertJsonFragment(['order_number' => $firstOrder->order_number])
            ->assertJsonMissing(['order_number' => 'ORD-2001']);
    }

    public function test_all_orders_endpoint_supports_filters(): void
    {
        [$business, $user] = $this->createBusinessUser('filtered-orders-owner@example.com');

        $this->createOrder($business, [
            'order_number' => 'ORD-PREPARING',
            'customer_name' => 'Akhil',
            'customer_phone' => '9876543210',
            'order_status' => 'preparing',
            'payment_status' => 'unpaid',
            'order_type' => 'dine_in',
        ]);

        $this->createOrder($business, [
            'order_number' => 'ORD-COMPLETE',
            'customer_name' => 'Riya',
            'customer_phone' => '9123456789',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'order_type' => 'takeaway',
        ]);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/orders/all?status=preparing&payment_status=unpaid&order_type=dine_in&search=Akhil')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_number', 'ORD-PREPARING')
            ->assertJsonMissing(['order_number' => 'ORD-COMPLETE']);
    }

    public function test_existing_orders_index_still_returns_paginated_orders(): void
    {
        [$business, $user] = $this->createBusinessUser('paginated-orders-owner@example.com');

        $this->createOrder($business, ['order_number' => 'ORD-PAGE-1']);
        $this->createOrder($business, ['order_number' => 'ORD-PAGE-2']);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/orders?per_page=1');

        $response->assertOk()
            ->assertJsonPath('message', 'Orders')
            ->assertJsonCount(1, 'data');
    }

    public function test_business_can_create_direct_order_without_qr_or_service_point(): void
    {
        [$business, $user] = $this->createBusinessUser('direct-orders-owner@example.com');
        $menuItem = $this->createMenuItem($business, [
            'name' => 'Veg Burger',
            'price' => 120,
            'tax_rate' => 5,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/orders/direct', [
                'customer_name' => 'Walk-in Customer',
                'customer_phone' => '9876543210',
                'payment_method' => 'cash',
                'notes' => 'Counter order',
                'items' => [
                    [
                        'menu_item_id' => $menuItem->id,
                        'quantity' => 2,
                        'client_price' => 1,
                    ],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Direct order created successfully')
            ->assertJsonPath('data.order_number', fn ($value) => preg_match('/^ORD-\d+$/', $value) === 1 && strlen($value) <= 10)
            ->assertJsonPath('data.display_order_id', fn ($value) => preg_match('/^#\d+$/', $value) === 1 && strlen($value) <= 7)
            ->assertJsonPath('data.order_type', 'takeaway')
            ->assertJsonPath('data.customer_name', 'Walk-in Customer')
            ->assertJsonPath('data.table_id', null)
            ->assertJsonPath('data.room_id', null)
            ->assertJsonPath('data.service_point_id', null)
            ->assertJsonPath('data.subtotal', 240)
            ->assertJsonPath('data.tax', 12)
            ->assertJsonPath('data.total', 252)
            ->assertJsonPath('data.items.0.item_name', 'Veg Burger')
            ->assertJsonPath('data.items.0.price', 120)
            ->assertJsonPath('data.payments.0.payment_method', 'cash');

        $this->assertDatabaseHas('orders', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'order_type' => 'takeaway',
            'customer_name' => 'Walk-in Customer',
            'total' => 252,
        ]);
    }

    public function test_direct_order_with_order_id_adds_items_to_existing_order(): void
    {
        [$business, $user] = $this->createBusinessUser('direct-existing-order-owner@example.com');
        $existingMenuItem = $this->createMenuItem($business, [
            'category' => 'Breakfast',
            'name' => 'Plain Dosa',
            'price' => 100,
            'tax_rate' => 0,
        ]);
        $newMenuItem = $this->createMenuItem($business, [
            'category' => 'Beverages',
            'name' => 'Masala Chai',
            'price' => 80,
            'tax_rate' => 10,
        ]);
        $order = $this->createOrder($business, [
            'subtotal' => 100,
            'tax' => 0,
            'total' => 100,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $existingMenuItem->id,
            'item_name' => 'Plain Dosa',
            'price' => 100,
            'quantity' => 1,
            'status' => 'preparing',
            'tax' => 0,
            'discount' => 0,
            'total' => 100,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/orders/direct', [
                'order_id' => $order->id,
                'items' => [
                    [
                        'menu_item_id' => $newMenuItem->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Items added to direct order')
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.subtotal', 260)
            ->assertJsonPath('data.tax', 16)
            ->assertJsonPath('data.total', 276)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonFragment([
                'item_name' => 'Masala Chai',
                'quantity' => 2,
            ]);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'business_id' => $business->id,
            'subtotal' => 260,
            'tax' => 16,
            'total' => 276,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $newMenuItem->id,
            'quantity' => 2,
            'total' => 176,
        ]);
    }

    public function test_direct_order_with_foreign_order_id_is_not_updated(): void
    {
        [$business, $user] = $this->createBusinessUser('direct-foreign-order-owner@example.com');
        [$otherBusiness] = $this->createBusinessUser('direct-foreign-other-owner@example.com');
        $menuItem = $this->createMenuItem($business, ['name' => 'Filter Coffee']);
        $otherOrder = $this->createOrder($otherBusiness);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/orders/direct', [
                'order_id' => $otherOrder->id,
                'items' => [
                    ['menu_item_id' => $menuItem->id, 'quantity' => 1],
                ],
            ]);

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Order not found');

        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_direct_order_rejects_menu_items_from_another_business(): void
    {
        [, $user] = $this->createBusinessUser('direct-isolation-owner@example.com');
        [$otherBusiness] = $this->createBusinessUser('direct-other-owner@example.com');
        $otherMenuItem = $this->createMenuItem($otherBusiness, ['name' => 'Other Pasta']);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/orders/direct', [
                'items' => [
                    ['menu_item_id' => $otherMenuItem->id, 'quantity' => 1],
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.items.0', 'One or more menu items are unavailable.');
    }

    private function createOrder(Business $business, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'business_id' => $business->id,
            'order_number' => 'ORD-'.uniqid(),
            'order_type' => 'dine_in',
            'customer_name' => 'Guest',
            'customer_phone' => null,
            'subtotal' => 100,
            'tax' => 0,
            'discount' => 0,
            'total' => 100,
            'payment_status' => 'unpaid',
            'order_status' => 'preparing',
            'notes' => null,
        ], $overrides));
    }

    private function createMenuItem(Business $business, array $overrides = []): MenuItem
    {
        $category = MenuCategory::create([
            'business_id' => $business->id,
            'name' => $overrides['category'] ?? 'Direct Orders',
            'code' => strtoupper(str_replace('.', '', uniqid('D', true))),
            'active' => true,
            'status' => 'active',
        ]);

        return MenuItem::create(array_merge([
            'business_id' => $business->id,
            'menu_category_id' => $category->id,
            'name' => 'Direct Order Item',
            'category' => $category->name,
            'type' => 'veg',
            'price' => 100,
            'tax_rate' => 0,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ], $overrides));
    }
}
