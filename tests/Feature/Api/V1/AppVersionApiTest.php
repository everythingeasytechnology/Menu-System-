<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;

class AppVersionApiTest extends ApiTestCase
{
    public function test_app_version_can_be_read_without_token(): void
    {
        $this->getJson('/api/v1/app-version')
            ->assertOk()
            ->assertJsonPath('message', 'App version')
            ->assertJsonPath('data.version', '1.0.0');
    }

    public function test_only_superadmin_can_update_app_version(): void
    {
        [, $owner] = $this->createBusinessUser('app-version-owner@example.com');

        $this->withHeaders($this->authHeaders($owner))
            ->postJson('/api/v1/app-version', [
                'version' => '1.2.0',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Only superadmin can update app version.');

        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'app-version-superadmin@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->withHeaders($this->authHeaders($superadmin))
            ->postJson('/api/v1/app-version', [
                'version' => '1.2.0',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'App version updated')
            ->assertJsonPath('data.version', '1.2.0');

        $this->getJson('/api/v1/app-version')
            ->assertOk()
            ->assertJsonPath('data.version', '1.2.0');
    }
}
