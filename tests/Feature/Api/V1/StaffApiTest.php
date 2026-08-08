<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffApiTest extends ApiTestCase
{
    public function test_can_create_list_update_and_deactivate_staff_member(): void
    {
        [$business, $owner] = $this->createBusinessUser('staff-owner@example.com');

        $createResponse = $this->withHeaders($this->authHeaders($owner))
            ->postJson('/api/v1/staff', [
                'name' => 'Rahul Waiter',
                'email' => 'rahul@example.com',
                'phone' => '+919999999999',
                'role' => 'waiter',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('message', 'Staff member created')
            ->assertJsonPath('data.business_id', $business->id)
            ->assertJsonPath('data.name', 'Rahul Waiter')
            ->assertJsonPath('data.role', 'waiter')
            ->assertJsonMissing(['password' => 'password123']);

        $staffId = $createResponse->json('data.id');
        $staff = User::findOrFail($staffId);

        $this->assertTrue(Hash::check('password123', $staff->password));

        $this->withHeaders($this->authHeaders($owner))
            ->getJson('/api/v1/staff?role=waiter')
            ->assertOk()
            ->assertJsonPath('data.0.id', $staffId);

        $this->withHeaders($this->authHeaders($owner))
            ->putJson('/api/v1/staff/'.$staffId, [
                'name' => 'Rahul Manager',
                'email' => 'rahul.manager@example.com',
                'role' => 'manager',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Rahul Manager')
            ->assertJsonPath('data.role', 'manager');

        $this->withHeaders($this->authHeaders($owner))
            ->postJson('/api/v1/staff/'.$staffId.'/status', [
                'status' => 'suspended',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->withHeaders($this->authHeaders($owner))
            ->deleteJson('/api/v1/staff/'.$staffId)
            ->assertOk()
            ->assertJsonPath('message', 'Staff member deactivated')
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_staff_api_is_business_isolated(): void
    {
        [, $ownerA] = $this->createBusinessUser('staff-a@example.com');
        [$businessB] = $this->createBusinessUser('staff-b@example.com');

        $otherStaff = User::create([
            'business_id' => $businessB->id,
            'name' => 'Other Staff',
            'email' => 'other-staff@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->withHeaders($this->authHeaders($ownerA))
            ->getJson('/api/v1/staff/'.$otherStaff->id)
            ->assertNotFound();

        $this->withHeaders($this->authHeaders($ownerA))
            ->putJson('/api/v1/staff/'.$otherStaff->id, [
                'name' => 'Changed',
            ])
            ->assertNotFound();
    }

    public function test_roles_endpoint_returns_supported_staff_roles(): void
    {
        [, $owner] = $this->createBusinessUser('roles@example.com');

        $this->withHeaders($this->authHeaders($owner))
            ->getJson('/api/v1/staff/roles')
            ->assertOk()
            ->assertJsonFragment(['value' => 'waiter'])
            ->assertJsonFragment(['value' => 'kitchen_staff']);
    }
}
