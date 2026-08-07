<?php

namespace Tests\Feature;

use App\Models\ServicePoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePointTest extends TestCase
{
    use RefreshDatabase;

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
}
