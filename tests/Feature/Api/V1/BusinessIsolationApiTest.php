<?php

namespace Tests\Feature\Api\V1;

use App\Models\MenuCategory;

class BusinessIsolationApiTest extends ApiTestCase
{
    public function test_business_a_cannot_fetch_business_b_category_by_id(): void
    {
        [$businessA, $userA] = $this->createBusinessUser('a@example.com');
        [$businessB] = $this->createBusinessUser('b@example.com');

        $ownCategory = MenuCategory::create([
            'business_id' => $businessA->id,
            'name' => 'Starters',
            'code' => 'STA',
            'active' => true,
            'status' => 'active',
        ]);

        $otherCategory = MenuCategory::create([
            'business_id' => $businessB->id,
            'name' => 'Desserts',
            'code' => 'DES',
            'active' => true,
            'status' => 'active',
        ]);

        $this->withHeaders($this->authHeaders($userA))
            ->getJson("/api/v1/categories/{$ownCategory->id}")
            ->assertOk();

        $this->withHeaders($this->authHeaders($userA))
            ->getJson("/api/v1/categories/{$otherCategory->id}")
            ->assertNotFound();
    }
}
