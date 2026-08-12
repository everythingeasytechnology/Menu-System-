<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ServicePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLiveOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->signInBusinessOwner('dashboard-orders-owner@example.com');
        $this->business = Business::create([
            'owner_user_id' => $this->owner->id,
            'name' => 'Dashboard Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $this->owner->update(['business_id' => $this->business->id]);
    }

    public function test_dashboard_uses_real_order_database_data(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-WEB-1001',
            'customer_name' => 'Akhil',
            'total' => 252,
            'order_status' => 'preparing',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Dashboard Cafe');
        $response->assertSee('ORD-WEB-1001');
        $response->assertSee('Rs. 252.00');
        $response->assertSee('Veg Burger');
    }

    public function test_live_orders_page_uses_real_order_database_data(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-WEB-1002',
            'customer_name' => 'Riya',
            'customer_phone' => '9123456789',
            'order_type' => 'takeaway',
            'total' => 500,
        ]);

        $response = $this->get('/orders');

        $response->assertOk();
        $response->assertSee('Live Orders');
        $response->assertSee('ORD-WEB-1002');
        $response->assertSee('Riya');
        $response->assertSee('Counter');
        $response->assertSee('Takeaway');
        $response->assertSee('Add Item');
        $response->assertSee('Category');
        $response->assertSee('Search Items');
        $response->assertSee('Item Status');
        $response->assertSee('Update Payment');
        $response->assertSee('Cash Paid');
    }

    public function test_live_orders_page_shows_variant_before_item_name(): void
    {
        $order = $this->createOrder([
            'order_number' => 'ORD-WEB-VARIANT',
            'customer_name' => 'Variant Guest',
            'total' => 320,
        ]);

        $order->items()->update([
            'item_name' => 'Paneer Tikka Special',
            'variant_label' => 'Large',
        ]);

        $this->get('/orders')
            ->assertOk()
            ->assertSee('Large Paneer Tikka Special');

        $this->getJson('/orders/live-feed')
            ->assertOk()
            ->assertJsonPath('orders.0.items.0.displayName', 'Large Paneer Tikka Special');
    }

    public function test_kitchen_display_screen_uses_live_database_orders(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-WEB-KDS',
            'customer_name' => 'Kitchen Guest',
            'order_status' => 'preparing',
            'notes' => 'No onion',
        ]);

        $response = $this->get('/kitchen-display');

        $response->assertOk();
        $response->assertSee('Kitchen Display');
        $response->assertSee('Dashboard Cafe live orders');
        $response->assertSee('ORD-WEB-KDS');
        $response->assertSee('Kitchen Guest');
        $response->assertSee('Veg Burger');
        $response->assertSee('No onion');
        $response->assertSee('Full Screen');
        $response->assertSee('Start');
        $response->assertSee('Ready');
        $response->assertSee('Served');
        $response->assertSee('preparing.png');
    }

    public function test_live_orders_feed_returns_database_orders_for_current_business(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-WEB-FEED',
            'customer_name' => 'Feed Customer',
        ]);

        $response = $this->getJson('/orders/live-feed');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('orders.0.orderNumber', 'ORD-WEB-FEED')
            ->assertJsonPath('orders.0.customer', 'Feed Customer');
    }

    public function test_live_orders_feed_includes_detailed_receipt_billing_data(): void
    {
        $coupon = Coupon::create([
            'business_id' => $this->business->id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order' => 0,
            'is_active' => true,
        ]);

        $this->createOrder([
            'order_number' => 'ORD-WEB-BILL',
            'coupon_id' => $coupon->id,
            'subtotal' => 240,
            'tax' => 12,
            'discount' => 24,
            'total' => 228,
        ]);

        $response = $this->getJson('/orders/live-feed');

        $response->assertOk()
            ->assertJsonPath('orders.0.orderNumber', 'ORD-WEB-BILL')
            ->assertJsonPath('orders.0.rawSubtotal', 240)
            ->assertJsonPath('orders.0.rawTax', 12)
            ->assertJsonPath('orders.0.rawDiscount', 24)
            ->assertJsonPath('orders.0.rawTotal', 228)
            ->assertJsonPath('orders.0.couponCode', 'SAVE10')
            ->assertJsonPath('orders.0.items.0.unitPrice', 120)
            ->assertJsonPath('orders.0.items.0.lineSubtotal', 240)
            ->assertJsonPath('orders.0.items.0.tax', 12)
            ->assertJsonPath('orders.0.items.0.total', 252);
    }

    public function test_order_status_update_persists_from_live_orders(): void
    {
        $order = $this->createOrder([
            'order_number' => 'ORD-WEB-STATUS',
            'order_status' => 'preparing',
        ]);

        $response = $this->postJson("/orders/{$order->id}/status", [
            'status' => 'ready',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', 'ready')
            ->assertJsonPath('order.statusLabel', 'Ready');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'ready',
        ]);
    }

    public function test_owner_can_mark_order_as_cash_paid_from_live_orders(): void
    {
        $order = $this->createOrder([
            'order_number' => 'ORD-WEB-CASH-PAID',
            'payment_status' => 'unpaid',
            'total' => 252,
        ]);

        $response = $this->postJson("/orders/{$order->id}/payment", [
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'amount' => 252,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.paymentStatus', 'paid')
            ->assertJsonPath('order.paymentMethod', 'cash')
            ->assertJsonPath('order.paymentLabel', 'Paid via Cash');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'business_id' => $this->business->id,
            'payment_method' => 'cash',
            'amount' => 252,
            'status' => 'paid',
        ]);
    }

    public function test_completed_paid_order_releases_service_point_and_leaves_live_orders(): void
    {
        $servicePoint = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'SP-RELEASE-1',
            'name' => 'Table 9',
            'seats' => 4,
            'category' => 'Dining',
            'point_type' => 'table',
            'status' => 'occupied',
            'is_active' => true,
            'order_number' => 'ORD-WEB-SP-RELEASE',
            'amount' => 252,
            'items' => [['name' => 'Veg Burger', 'qty' => 2]],
        ]);
        $order = $this->createOrder([
            'service_point_id' => $servicePoint->id,
            'order_number' => 'ORD-WEB-SP-RELEASE',
            'order_status' => 'served',
            'payment_status' => 'unpaid',
            'total' => 252,
        ]);

        $statusResponse = $this->postJson("/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);

        $statusResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', 'completed')
            ->assertJsonPath('order.paymentStatus', 'pending');

        // Completed orders leave the live feed immediately, regardless of payment status.
        $this->getJson('/orders/live-feed?active_only=1')
            ->assertOk()
            ->assertJsonMissing(['orderNumber' => 'ORD-WEB-SP-RELEASE']);

        $paymentResponse = $this->postJson("/orders/{$order->id}/payment", [
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'amount' => 252,
        ]);

        $paymentResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', 'completed')
            ->assertJsonPath('order.paymentStatus', 'paid');

        $this->assertDatabaseHas('service_points', [
            'id' => $servicePoint->id,
            'status' => 'available',
            'order_number' => null,
            'amount' => 0,
            'items' => null,
        ]);
        $this->getJson('/orders/live-feed?active_only=1')
            ->assertOk()
            ->assertJsonMissing(['orderNumber' => 'ORD-WEB-SP-RELEASE']);
        $this->get('/orders')->assertOk()->assertDontSee('ORD-WEB-SP-RELEASE');
    }

    public function test_order_item_status_update_persists_and_recalculates_order_status(): void
    {
        $order = $this->createOrder([
            'order_number' => 'ORD-WEB-ITEM-STATUS',
            'order_status' => 'preparing',
        ]);
        $firstItem = $order->items()->first();
        $secondItem = $order->items()->create([
            'item_name' => 'Chicken Wings',
            'variant_label' => null,
            'price' => 180,
            'quantity' => 1,
            'status' => 'preparing',
            'tax' => 9,
            'discount' => 0,
            'total' => 189,
        ]);

        $response = $this->postJson("/orders/{$order->id}/items/{$firstItem->id}/status", [
            'status' => 'preparing',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.status', 'preparing');

        $responseItems = collect($response->json('order.items'))->keyBy('id');
        $this->assertSame('preparing', $responseItems[$firstItem->id]['status']);
        $this->assertSame('preparing', $responseItems[$secondItem->id]['status']);

        $this->assertDatabaseHas('order_items', [
            'id' => $firstItem->id,
            'status' => 'preparing',
        ]);

        $this->postJson("/orders/{$order->id}/items/{$firstItem->id}/status", ['status' => 'served'])->assertOk();
        $readyResponse = $this->postJson("/orders/{$order->id}/items/{$secondItem->id}/status", ['status' => 'ready']);

        $readyResponse->assertOk()
            ->assertJsonPath('order.status', 'ready');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'ready',
        ]);
    }

    public function test_owner_can_add_menu_item_to_live_order_from_dashboard(): void
    {
        $order = $this->createOrder([
            'order_number' => 'ORD-WEB-ADD',
            'subtotal' => 240,
            'tax' => 12,
            'discount' => 0,
            'total' => 252,
        ]);
        $menuItem = $this->createMenuItem([
            'name' => 'Masala Fries',
            'price' => 100,
            'tax_rate' => 5,
        ]);

        $response = $this->postJson("/orders/{$order->id}/items", [
            'menu_item_id' => $menuItem->id,
            'quantity' => 2,
            'special_instructions' => 'Less salt',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.itemCount', 2)
            ->assertJsonPath('order.rawTotal', 462);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'item_name' => 'Masala Fries',
            'quantity' => 2,
            'tax' => 10,
            'total' => 210,
            'special_instructions' => 'Less salt',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'subtotal' => 440,
            'tax' => 22,
            'total' => 462,
        ]);
    }

    public function test_owner_cannot_add_another_business_menu_item_to_order(): void
    {
        $order = $this->createOrder(['order_number' => 'ORD-WEB-OTHER']);
        $otherBusiness = Business::create([
            'owner_user_id' => $this->owner->id,
            'name' => 'Other Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $menuItem = MenuItem::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Fries',
            'category' => 'Snacks',
            'type' => 'veg',
            'price' => 100,
            'tax_rate' => 0,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ]);

        $response = $this->postJson("/orders/{$order->id}/items", [
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.items.0', 'One or more menu items are unavailable.');

        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
        ]);
    }

    private function createOrder(array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-WEB-'.uniqid(),
            'order_type' => 'takeaway',
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => '9876543210',
            'subtotal' => 240,
            'tax' => 12,
            'discount' => 0,
            'total' => 252,
            'payment_status' => 'unpaid',
            'order_status' => 'preparing',
            'notes' => 'Less spicy',
        ], $overrides));

        $order->items()->create([
            'item_name' => 'Veg Burger',
            'variant_label' => null,
            'price' => 120,
            'quantity' => 2,
            'status' => $overrides['order_status'] ?? 'preparing',
            'tax' => 12,
            'discount' => 0,
            'total' => 252,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'business_id' => $this->business->id,
            'payment_method' => 'cash',
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        return $order;
    }

    private function createMenuItem(array $overrides = []): MenuItem
    {
        return MenuItem::create(array_merge([
            'business_id' => $this->business->id,
            'name' => 'Order Add Item',
            'category' => 'Snacks',
            'type' => 'veg',
            'price' => 100,
            'tax_rate' => 0,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ], $overrides));
    }
}
