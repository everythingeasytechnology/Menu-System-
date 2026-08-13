<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\PresetFoodImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->signInBusinessOwner('menu-owner@example.com');
        $this->business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Current Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);
        $owner->update(['business_id' => $this->business->id]);
    }

    /**
     * Test the index route returns items.
     */
    public function test_menu_index_loads_items(): void
    {
        $item = MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Paneer Tikka',
            'category' => 'Starters',
            'type' => 'veg',
            'cooking_time' => '10 mins',
            'stock' => true,
        ]);

        $item->variants()->create([
            'label' => 'Single Portion',
            'price' => 180.00,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('Paneer Tikka');
        $response->assertSee('Single Portion');
    }

    public function test_menu_index_only_loads_items_for_current_business(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Other Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Current Business Noodles',
            'category' => 'Mains',
            'type' => 'veg',
            'stock' => true,
        ]);

        MenuItem::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Business Burger',
            'category' => 'Mains',
            'type' => 'non-veg',
            'stock' => true,
        ]);

        $response = $this->get('/menu');

        $response->assertOk();
        $response->assertSee('Current Business Noodles');
        $response->assertDontSee('Other Business Burger');
    }

    public function test_menu_items_feed_is_debounced_search_source_and_business_scoped(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Other Search Restaurant',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Paneer Roll',
            'category' => 'Snacks',
            'type' => 'veg',
            'stock' => true,
        ]);

        MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Masala Dosa',
            'category' => 'South Indian',
            'type' => 'veg',
            'stock' => true,
        ]);

        MenuItem::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Paneer From Other Business',
            'category' => 'Snacks',
            'type' => 'veg',
            'stock' => true,
        ]);

        $response = $this->getJson('/menu/items?search=paneer');

        $response->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.name', 'Paneer Roll')
            ->assertJsonMissing(['name' => 'Paneer From Other Business'])
            ->assertJsonPath('limit', 80);
    }

    /**
     * Test adding a menu item with variants.
     */
    public function test_can_add_menu_item_with_variants(): void
    {
        $preset = PresetFoodImage::create([
            'name' => 'Steak',
            'tags' => 'steak',
            'image_path' => 'images/defaults/steak.jpg',
        ]);

        $data = [
            'name' => 'Chicken Tikka',
            'category' => 'Starters',
            'type' => 'non-veg',
            'cooking_time' => '15 mins',
            'preset_image_id' => $preset->id,
            'variants' => [
                ['label' => 'Half Portion', 'price' => '200.00'],
                ['label' => 'Full Portion', 'price' => '380.00'],
            ],
        ];

        $response = $this->post('/menu', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_items', [
            'business_id' => $this->business->id,
            'name' => 'Chicken Tikka',
            'type' => 'non-veg',
            'preset_food_image_id' => $preset->id,
        ]);

        $item = MenuItem::where('name', 'Chicken Tikka')->first();
        $this->assertCount(2, $item->variants);
        $this->assertDatabaseHas('menu_item_variants', [
            'menu_item_id' => $item->id,
            'label' => 'Half Portion',
            'price' => 200.00,
        ]);
    }

    /**
     * Test toggling stock status.
     */
    public function test_can_toggle_menu_item_stock(): void
    {
        $item = MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Lemon Tea',
            'category' => 'Beverages',
            'type' => 'veg',
            'cooking_time' => '3 mins',
            'stock' => true,
        ]);

        $response = $this->post("/menu/{$item->id}/toggle-stock");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'stock' => false]);

        $item->refresh();
        $this->assertFalse($item->stock);
    }

    /**
     * Test updating a menu item.
     */
    public function test_can_update_menu_item_and_variants(): void
    {
        $preset = PresetFoodImage::create([
            'name' => 'Pizza',
            'tags' => 'pizza',
            'image_path' => 'images/defaults/pizza.jpg',
        ]);

        $item = MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Old Butter Chicken',
            'category' => 'Non-Veg Main Course',
            'type' => 'non-veg',
            'cooking_time' => '25 mins',
            'stock' => true,
        ]);

        $item->variants()->create([
            'label' => 'Single Portion',
            'price' => 300.00,
        ]);

        $updateData = [
            'name' => 'New Butter Chicken',
            'category' => 'Non-Veg Main Course',
            'type' => 'non-veg',
            'cooking_time' => '20 mins',
            'preset_image_id' => $preset->id,
            'variants' => [
                ['label' => 'Half Portion', 'price' => '220.00'],
                ['label' => 'Full Portion', 'price' => '420.00'],
            ],
        ];

        $response = $this->put("/menu/{$item->id}", $updateData);

        $response->assertRedirect();

        $this->assertDatabaseHas('menu_items', [
            'id' => $item->id,
            'name' => 'New Butter Chicken',
            'cooking_time' => '20 mins',
            'preset_food_image_id' => $preset->id,
        ]);

        $item->refresh();
        $this->assertCount(2, $item->variants);
        $this->assertDatabaseHas('menu_item_variants', [
            'menu_item_id' => $item->id,
            'label' => 'Full Portion',
            'price' => 420.00,
        ]);
        $this->assertDatabaseMissing('menu_item_variants', [
            'label' => 'Single Portion',
        ]);
    }

    /**
     * Test searching preset food images.
     */
    public function test_can_search_preset_images(): void
    {
        PresetFoodImage::create([
            'name' => 'Paneer Butter Masala',
            'tags' => 'paneer, veg, indian',
            'image_path' => 'images/defaults/salad.jpg',
        ]);

        PresetFoodImage::create([
            'name' => 'Gourmet Ribeye Steak',
            'tags' => 'steak, beef, meat, non-veg',
            'image_path' => 'images/defaults/steak.jpg',
        ]);

        $response = $this->get('/preset-images?search=paneer');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Paneer Butter Masala',
            'image_path' => 'images/defaults/salad.jpg',
        ]);
        $response->assertJsonMissing([
            'name' => 'Gourmet Ribeye Steak',
        ]);
    }

    /**
     * Test deleting a menu item.
     */
    public function test_can_delete_menu_item(): void
    {
        $item = MenuItem::create([
            'business_id' => $this->business->id,
            'name' => 'Cold Coffee',
            'category' => 'Beverages',
            'type' => 'veg',
            'cooking_time' => '5 mins',
            'stock' => true,
        ]);

        $variant = $item->variants()->create([
            'label' => 'Regular',
            'price' => 120.00,
        ]);

        $response = $this->delete("/menu/{$item->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('menu_item_variants', ['id' => $variant->id]);
    }
}
