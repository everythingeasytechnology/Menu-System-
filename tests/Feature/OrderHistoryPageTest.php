<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServicePoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryPageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->signInBusinessOwner('history-owner@example.com');
        $this->business = Business::create([
            'owner_user_id' => $this->owner->id,
            'name' => 'History Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $this->owner->update(['business_id' => $this->business->id]);
    }

    public function test_order_history_page_lists_all_business_orders(): void
    {
        $this->createOrder([
            'order_number' => 'ORD-HIST-PAID',
            'customer_name' => 'Priya',
            'order_status' => 'completed',
            'payment_status' => 'paid',
        ]);
        $this->createOrder([
            'order_number' => 'ORD-HIST-LIVE',
            'customer_name' => 'Akhil',
            'order_status' => 'preparing',
            'payment_status' => 'pending',
        ]);
        $otherBusiness = Business::create([
            'owner_user_id' => $this->owner->id,
            'name' => 'Other History Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $this->createOrder([
            'business_id' => $otherBusiness->id,
            'order_number' => 'ORD-HIST-OTHER',
            'customer_name' => 'Other Customer',
        ]);

        $response = $this->get('/orders/history');

        $response->assertOk();
        $response->assertSee('Order History');
        $response->assertSee('ORD-HIST-PAID');
        $response->assertSee('ORD-HIST-LIVE');
        $response->assertDontSee('ORD-HIST-OTHER');
        $response->assertSee('Filtered Orders');
        $response->assertSee('Paid Collection');
    }

    public function test_order_history_filters_by_date_status_payment_type_service_point_and_search(): void
    {
        $servicePoint = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'SP-HISTORY-1',
            'name' => 'Table 12',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);
        $matched = $this->createOrder([
            'service_point_id' => $servicePoint->id,
            'order_number' => 'ORD-HIST-MATCH',
            'customer_name' => 'Priya History',
            'order_type' => 'dine_in',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'total' => 550,
        ]);
        $matched->forceFill([
            'created_at' => now()->subDays(3)->setTime(13, 15),
            'updated_at' => now()->subDays(3)->setTime(13, 20),
        ])->save();
        $excluded = $this->createOrder([
            'order_number' => 'ORD-HIST-EXCLUDED',
            'customer_name' => 'Riya Pending',
            'order_type' => 'takeaway',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'total' => 250,
        ]);
        $excluded->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();
        $date = $matched->created_at->toDateString();

        $response = $this->get('/orders/history?'.http_build_query([
            'search' => 'Priya History',
            'from' => $date,
            'to' => $date,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_type' => 'dine_in',
            'service_point_id' => $servicePoint->id,
        ]));

        $response->assertOk();
        $response->assertSee('ORD-HIST-MATCH');
        $response->assertSee('Table 12');
        $response->assertSee('Rs. 550.00');
        $response->assertDontSee('ORD-HIST-EXCLUDED');
    }

    private function createOrder(array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-HIST-'.uniqid(),
            'order_type' => 'takeaway',
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => '9876543210',
            'subtotal' => 240,
            'tax' => 12,
            'discount' => 0,
            'total' => 252,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'notes' => 'History note',
        ], $overrides));

        $order->items()->create([
            'item_name' => 'Veg Burger',
            'variant_label' => null,
            'price' => 120,
            'quantity' => 2,
            'status' => $order->order_status,
            'tax' => 12,
            'discount' => 0,
            'total' => $order->total,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'business_id' => $order->business_id,
            'payment_method' => 'cash',
            'amount' => $order->total,
            'status' => $order->payment_status === 'paid' ? 'paid' : 'pending',
            'paid_at' => $order->payment_status === 'paid' ? now() : null,
        ]);

        return $order;
    }
}
