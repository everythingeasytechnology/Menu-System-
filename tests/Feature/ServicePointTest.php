<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServicePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePointTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->signInBusinessOwner('service-point-owner@example.com');
        $this->business = Business::create([
            'owner_user_id' => $this->owner->id,
            'name' => 'Service Point Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $this->owner->update(['business_id' => $this->business->id]);
    }

    /**
     * Test index lists points and categories.
     */
    public function test_service_points_index_loads_correctly(): void
    {
        ServicePoint::create([
            'code' => 'SP-9999',
            'name' => 'Premium Cabin',
            'seats' => 4,
            'category' => 'VVIP Lounges',
            'status' => 'available',
        ]);

        $response = $this->get('/service-points');

        $response->assertStatus(200);
        $response->assertSee('Premium Cabin');
        $response->assertSee('SP-9999');
        $response->assertSee('VVIP Lounges');
    }

    /**
     * Test storing service point auto-generates code.
     */
    public function test_can_create_service_point_with_auto_generated_code(): void
    {
        $data = [
            'name' => 'Special Table A',
            'seats' => 6,
            'category' => 'Terrace Garden',
        ];

        $response = $this->post('/service-points', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_points', [
            'name' => 'Special Table A',
            'code' => 'SP-0001',
            'business_id' => $this->business->id,
            'seats' => 6,
            'category' => 'Terrace Garden',
        ]);
    }

    /**
     * Test updating state.
     */
    public function test_can_update_service_point_state(): void
    {
        $point = ServicePoint::create([
            'code' => 'SP-0001',
            'name' => 'Table 1',
            'seats' => 4,
            'category' => 'Dining Hall',
            'status' => 'available',
        ]);

        $updateData = [
            'status' => 'occupied',
            'items' => ['1x Cheeseburger', '1x Coke'],
            'amount' => 250.00,
        ];

        $response = $this->put("/service-points/{$point->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonPath('point.status', 'occupied');
        $response->assertJsonPath('point.amount', 250);

        $point->refresh();
        $this->assertEquals('occupied', $point->status);
        $this->assertCount(2, $point->items);
        $this->assertEquals(250.00, $point->amount);
    }

    public function test_service_point_scanner_renders_branded_qr_png(): void
    {
        $point = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'SP-SCAN-1',
            'qr_identifier' => 'sp_scan_test',
            'name' => 'Scanner Table',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->get("/service-points/{$point->id}/scanner");

        $response->assertOk();
        $this->assertStringContainsString('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith("\x89PNG\r\n\x1A\n", $response->getContent());

        $download = $this->get("/service-points/{$point->id}/scanner?download=1");

        $download->assertOk();
        $this->assertStringContainsString('attachment', $download->headers->get('Content-Disposition'));
    }

    public function test_checkout_settle_completes_paid_orders_and_frees_service_point(): void
    {
        $point = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'SP-SETTLE-1',
            'qr_identifier' => 'sp_settle_test',
            'name' => 'Table 7',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'occupied',
            'is_active' => true,
            'order_number' => '2 active orders',
            'amount' => 500,
            'items' => [['label' => 'Legacy Item']],
        ]);
        $firstOrder = $this->createOrderForPoint($point, [
            'order_number' => 'ORD-SP-SETTLE-1',
            'total' => 252,
            'order_status' => 'served',
            'payment_status' => 'unpaid',
        ]);
        $secondOrder = $this->createOrderForPoint($point, [
            'order_number' => 'ORD-SP-SETTLE-2',
            'total' => 189,
            'order_status' => 'ready',
            'payment_status' => 'pending',
        ]);

        $response = $this->postJson("/service-points/{$point->id}/settle");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('settled_order_count', 2)
            ->assertJsonPath('point.status', 'available')
            ->assertJsonPath('point.amount', 0)
            ->assertJsonPath('point.active_order_count', 0);

        foreach ([$firstOrder, $secondOrder] as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'order_status' => 'completed',
                'payment_status' => 'paid',
            ]);

            $this->assertDatabaseHas('payments', [
                'order_id' => $order->id,
                'business_id' => $this->business->id,
                'payment_method' => 'cash',
                'status' => 'paid',
            ]);
        }

        $this->assertDatabaseHas('service_points', [
            'id' => $point->id,
            'status' => 'available',
            'order_number' => null,
            'amount' => 0,
            'items' => null,
        ]);
    }

    /**
     * Test deleting service point.
     */
    public function test_can_delete_service_point(): void
    {
        $point = ServicePoint::create([
            'code' => 'SP-0001',
            'name' => 'Table 1',
            'seats' => 4,
            'category' => 'Dining Hall',
            'status' => 'available',
        ]);

        $response = $this->delete("/service-points/{$point->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('service_points', ['id' => $point->id]);
    }

    private function createOrderForPoint(ServicePoint $point, array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'business_id' => $this->business->id,
            'service_point_id' => $point->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-SP-'.uniqid(),
            'order_type' => 'dine_in',
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => '9876543210',
            'subtotal' => 240,
            'tax' => 12,
            'discount' => 0,
            'total' => 252,
            'payment_status' => 'unpaid',
            'order_status' => 'preparing',
            'notes' => null,
        ], $overrides));

        $order->items()->create([
            'item_name' => 'Veg Burger',
            'variant_label' => null,
            'price' => $order->total,
            'quantity' => 1,
            'status' => $order->order_status,
            'tax' => 0,
            'discount' => 0,
            'total' => $order->total,
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
}
