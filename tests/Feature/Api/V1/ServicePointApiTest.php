<?php

namespace Tests\Feature\Api\V1;

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

        $download = $this->withHeaders($this->authHeaders($user))
            ->get($response->json('data.scanner_download_url'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);

        $this->assertStringContainsString('scanner.svg', $download->headers->get('Content-Disposition'));
    }
}
