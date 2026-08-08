<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->signInBusinessOwner('coupon-owner@example.com');
    }

    public function test_coupons_dashboard_loads_and_attaches_owner_business(): void
    {
        $response = $this->get('/coupons');

        $response->assertOk();
        $response->assertSee('Coupon Studio');
        $response->assertSee('Create Coupon');
        $this->assertNotNull($this->owner->fresh()->business_id);
    }

    public function test_owner_can_create_coupon_from_dashboard(): void
    {
        $response = $this->post('/coupons', [
            'code' => 'save10',
            'type' => 'percentage',
            'value' => '10',
            'minimum_order' => '100',
            'maximum_discount' => '50',
            'usage_limit' => '25',
            'per_user_limit' => '1',
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'expires_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('dashboard.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'business_id' => $this->owner->fresh()->business_id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_update_and_deactivate_coupon(): void
    {
        $this->get('/coupons');

        $coupon = Coupon::create([
            'business_id' => $this->owner->fresh()->business_id,
            'code' => 'WELCOME',
            'type' => 'fixed',
            'value' => 100,
            'minimum_order' => 0,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $updateResponse = $this->put("/coupons/{$coupon->id}", [
            'code' => 'welcome25',
            'type' => 'percentage',
            'value' => '25',
            'minimum_order' => '200',
            'maximum_discount' => '75',
            'usage_limit' => '30',
            'per_user_limit' => '2',
            'starts_at' => '',
            'expires_at' => '',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('dashboard.coupons.index'));
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'WELCOME25',
            'type' => 'percentage',
            'is_active' => true,
        ]);

        $deleteResponse = $this->delete("/coupons/{$coupon->id}");

        $deleteResponse->assertRedirect(route('dashboard.coupons.index'));
        $this->assertFalse($coupon->fresh()->is_active);
    }

    public function test_owner_cannot_update_coupon_from_another_business(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Other Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $coupon = Coupon::create([
            'business_id' => $otherBusiness->id,
            'code' => 'OTHER',
            'type' => 'fixed',
            'value' => 50,
            'minimum_order' => 0,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->put("/coupons/{$coupon->id}", [
            'code' => 'OTHER2',
            'type' => 'fixed',
            'value' => '75',
            'minimum_order' => '0',
            'is_active' => '1',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'OTHER',
        ]);
    }
}
