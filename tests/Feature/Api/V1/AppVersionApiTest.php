<?php

namespace Tests\Feature\Api\V1;

class AppVersionApiTest extends ApiTestCase
{
    public function test_app_version_can_be_read_without_token(): void
    {
        $this->getJson('/api/v1/app-version')
            ->assertOk()
            ->assertJsonPath('message', 'App version')
            ->assertJsonPath('data.version', '1.0.0');
    }

    public function test_app_version_api_does_not_update_version(): void
    {
        $this->postJson('/api/v1/app-version', [
            'version' => '1.2.0',
        ])
            ->assertMethodNotAllowed();

        $this->getJson('/api/v1/app-version')
            ->assertOk()
            ->assertJsonPath('data.version', '1.0.0');
    }
}
