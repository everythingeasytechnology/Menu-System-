<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Business $otherBusiness;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->signInBusinessOwner('reports-owner@example.com');
        $this->business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'My Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $owner->update(['business_id' => $this->business->id]);

        $otherOwner = \App\Models\User::create([
            'name' => 'Other Owner',
            'email' => 'other-owner@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->otherBusiness = Business::create([
            'owner_user_id' => $otherOwner->id,
            'name' => 'Someone Elses Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $otherOwner->update(['business_id' => $this->otherBusiness->id]);
    }

    public function test_reports_page_loads_with_no_data(): void
    {
        $response = $this->get('/reports');

        $response->assertOk();
        $response->assertSee('Reports Dashboard');
        $response->assertDontSee('Staff Report');
    }

    public function test_reports_shows_only_current_business_data(): void
    {
        $order = Order::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-1001',
            'order_type' => 'dine_in',
            'subtotal' => 500,
            'tax' => 0,
            'discount' => 0,
            'total' => 500,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Paneer Tikka',
            'quantity' => 2,
            'price' => 250,
            'total' => 500,
        ]);

        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => 500,
            'status' => 'paid',
        ]);

        // Data belonging to another business — must never leak into this report.
        $otherOrder = Order::create([
            'business_id' => $this->otherBusiness->id,
            'order_number' => 'ORD-9999',
            'order_type' => 'takeaway',
            'subtotal' => 9000,
            'tax' => 0,
            'discount' => 0,
            'total' => 9000,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        Payment::create([
            'business_id' => $this->otherBusiness->id,
            'order_id' => $otherOrder->id,
            'payment_method' => 'online',
            'amount' => 9000,
            'status' => 'paid',
        ]);

        $response = $this->get('/reports?period=7days');

        $response->assertOk();
        $response->assertSee('500.00');
        $response->assertDontSee('9000.00');
        $response->assertDontSee('Staff Report');
        $response->assertDontSee('staffPerformance');
    }

    public function test_reports_period_filter_is_respected(): void
    {
        $oldOrder = Order::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-OLD',
            'order_type' => 'dine_in',
            'subtotal' => 750,
            'tax' => 0,
            'discount' => 0,
            'total' => 750,
            'payment_status' => 'paid',
            'order_status' => 'completed',
        ]);
        $oldOrder->forceFill([
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ])->save();

        $response = $this->get('/reports?period=today');

        $response->assertOk();
        $response->assertDontSee('750.00');
    }

    public function test_reports_excludes_cancelled_orders_from_revenue(): void
    {
        Order::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-CANCEL',
            'order_type' => 'dine_in',
            'subtotal' => 300,
            'tax' => 0,
            'discount' => 0,
            'total' => 300,
            'payment_status' => 'pending',
            'order_status' => 'cancelled',
        ]);

        $response = $this->get('/reports?period=7days');

        $response->assertOk();
        $response->assertSee('₹ 0.00');
    }
}
