<?php

namespace Tests\Feature\Api\V1;

class MenuApiTest extends ApiTestCase
{
    public function test_can_create_category_and_menu_item_for_authenticated_business(): void
    {
        [$business, $user] = $this->createBusinessUser();

        $categoryResponse = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/categories', [
                'name' => 'Beverages',
                'description' => 'Drinks',
            ]);

        $categoryResponse->assertCreated()
            ->assertJsonPath('data.name', 'Beverages');

        $itemResponse = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/menu-items', [
                'category_id' => $categoryResponse->json('data.id'),
                'name' => 'Cold Coffee',
                'description' => 'Iced coffee',
                'type' => 'veg',
                'price' => 120,
                'tax_rate' => 5,
                'variants' => [
                    ['label' => 'Regular', 'price' => 120],
                    ['label' => 'Large', 'price' => 160],
                ],
            ]);

        $itemResponse->assertCreated()
            ->assertJsonPath('data.name', 'Cold Coffee')
            ->assertJsonPath('data.variants.1.price', 160);

        $this->assertDatabaseHas('menu_items', [
            'business_id' => $business->id,
            'name' => 'Cold Coffee',
            'price' => 120,
        ]);
    }

    public function test_can_filter_menu_items_by_type_and_category(): void
    {
        [, $user] = $this->createBusinessUser('filter@example.com');

        $vegCategory = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/categories', ['name' => 'Veg Starters'])
            ->json('data.id');

        $nonVegCategory = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/categories', ['name' => 'Non Veg Starters'])
            ->json('data.id');

        $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/menu-items', [
                'category_id' => $vegCategory,
                'name' => 'Paneer Tikka',
                'type' => 'veg',
                'price' => 220,
            ]);

        $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/menu-items', [
                'category_id' => $nonVegCategory,
                'name' => 'Chicken Tikka',
                'type' => 'non-veg',
                'price' => 260,
            ]);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/menu-items/filter?type=veg&category_id='.$vegCategory)
            ->assertOk()
            ->assertJsonPath('message', 'Filtered menu items')
            ->assertJsonPath('data.0.name', 'Paneer Tikka')
            ->assertJsonMissing(['name' => 'Chicken Tikka']);

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/menu-items?type=non-veg&category=Non%20Veg%20Starters')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Chicken Tikka')
            ->assertJsonMissing(['name' => 'Paneer Tikka']);
    }

    public function test_menu_item_response_uses_current_category_name_from_category_table(): void
    {
        [, $user] = $this->createBusinessUser('category-name-response@example.com');

        $categoryId = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/categories', ['name' => 'Pizza'])
            ->json('data.id');

        $itemId = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/menu-items', [
                'category_id' => $categoryId,
                'name' => 'Margherita',
                'type' => 'veg',
                'price' => 250,
            ])
            ->json('data.id');

        $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/categories/'.$categoryId, ['name' => 'Updated Pizza'])
            ->assertOk();

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/menu-items/'.$itemId)
            ->assertOk()
            ->assertJsonPath('data.category_id', $categoryId)
            ->assertJsonPath('data.category', 'Updated Pizza');
    }
}
