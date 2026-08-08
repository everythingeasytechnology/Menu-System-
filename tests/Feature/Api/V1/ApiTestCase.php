<?php

namespace Tests\Feature\Api\V1;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function createBusinessUser(string $email = 'owner@example.com'): array
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => $email,
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => 'Test Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return [$business, $user->fresh()];
    }

    protected function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createApiToken('phpunit')];
    }
}
