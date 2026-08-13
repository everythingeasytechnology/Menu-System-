<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\ServicePoint;

class ServicePointApiTest extends ApiTestCase
{
    public function test_can_create_service_point_with_generated_code_and_qr(): void
    {
        [$business, $user] = $this->createBusinessUser();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/service-points', [
                'name' => 'Table 7',
                'seats' => 4,
                'category' => 'Dining Hall',
                'point_type' => 'table',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Service point created')
            ->assertJsonPath('data.name', 'Table 7')
            ->assertJsonPath('data.category', 'Dining Hall')
            ->assertJsonPath('data.point_type', 'table')
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.code', fn ($value) => str_starts_with($value, 'SP-'))
            ->assertJsonPath('data.qr_identifier', fn ($value) => str_starts_with($value, 'sp_'))
            ->assertJsonPath('data.scan_url', fn ($value) => str_contains($value, '/api/v1/public/menu/sp_'))
            ->assertJsonPath('data.scanner_download_url', fn ($value) => str_contains($value, '/api/v1/service-points/'));

        $this->assertDatabaseHas('service_points', [
            'business_id' => $business->id,
            'name' => 'Table 7',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);

        $download = $this->get($response->json('data.scanner_download_url'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);

        $this->assertStringContainsString('scanner.svg', $download->headers->get('Content-Disposition'));
    }

    public function test_service_point_scan_url_can_point_to_react_customer_menu(): void
    {
        config(['app.customer_menu_url' => 'https://menu.example.com/menu?point={qr}']);
        [, $user] = $this->createBusinessUser('react-menu-owner@example.com');

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/service-points', [
                'name' => 'React Table',
                'seats' => 4,
                'category' => 'Dining Hall',
            ]);

        $qrIdentifier = $response->json('data.qr_identifier');

        $response->assertCreated()
            ->assertJsonPath('data.scan_url', 'https://menu.example.com/menu?point='.$qrIdentifier);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/service-points/'.$response->json('data.id'))
            ->assertOk()
            ->assertJsonPath('data.scan_url', 'https://menu.example.com/menu?point='.$qrIdentifier);
    }

    public function test_service_point_api_reports_occupied_from_active_orders(): void
    {
        [$business, $user] = $this->createBusinessUser('occupied-service-point-owner@example.com');
        $occupiedPoint = ServicePoint::create([
            'business_id' => $business->id,
            'code' => 'SP-OCCUPIED',
            'qr_identifier' => 'sp_occupied_table',
            'name' => 'Table 10',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);
        $availablePoint = ServicePoint::create([
            'business_id' => $business->id,
            'code' => 'SP-AVAILABLE',
            'qr_identifier' => 'sp_available_table',
            'name' => 'Table 11',
            'seats' => 4,
            'category' => 'Dining Hall',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);
        $order = Order::create([
            'business_id' => $business->id,
            'service_point_id' => $occupiedPoint->id,
            'order_number' => 'ORD-SP-ACTIVE',
            'order_type' => 'dine_in',
            'subtotal' => 450,
            'tax' => 0,
            'discount' => 0,
            'total' => 450,
            'payment_status' => 'unpaid',
            'order_status' => 'preparing',
        ]);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/service-points/'.$occupiedPoint->id)
            ->assertOk()
            ->assertJsonPath('data.id', $occupiedPoint->id)
            ->assertJsonPath('data.status', 'occupied')
            ->assertJsonPath('data.amount', 450)
            ->assertJsonPath('data.active_order_count', 1)
            ->assertJsonPath('data.active_orders.0.id', $order->id)
            ->assertJsonPath('data.active_orders.0.order_number', 'ORD-SP-ACTIVE');

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/service-points?status=occupied')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $occupiedPoint->id);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/service-points?status=available')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $availablePoint->id);
    }
}
