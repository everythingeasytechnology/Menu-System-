<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInBusinessOwner('category-owner@example.com');
    }

    /**
     * Test categories index page lists categories.
     */
    public function test_categories_index_loads_categories(): void
    {
        $cat = MenuCategory::create([
            'name' => 'Italian Specials',
            'code' => 'ITA',
            'active' => true,
        ]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Italian Specials');
        $response->assertSee('ITA');
    }

    /**
     * Test adding a category.
     */
    public function test_can_create_category(): void
    {
        $data = [
            'name' => 'Soups',
        ];

        $response = $this->post('/categories', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('menu_categories', [
            'name' => 'Soups',
            'code' => 'SOU',
            'active' => true,
        ]);
    }

    /**
     * Test updating a category updates associated menu items.
     */
    public function test_can_update_category_and_rename_associated_items(): void
    {
        $cat = MenuCategory::create([
            'name' => 'old_name',
            'code' => 'OLD',
            'active' => true,
        ]);

        $item = MenuItem::create([
            'name' => 'Tomato Soup',
            'category' => 'old_name',
            'type' => 'veg',
            'cooking_time' => '10 mins',
            'stock' => true,
        ]);

        $updateData = [
            'name' => 'new_name',
        ];

        $response = $this->put("/categories/{$cat->id}", $updateData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('menu_categories', [
            'id' => $cat->id,
            'name' => 'new_name',
            'code' => 'NEW',
        ]);

        $item->refresh();
        $this->assertEquals('new_name', $item->category);
    }

    /**
     * Test toggling category active visibility status.
     */
    public function test_can_toggle_category_active(): void
    {
        $cat = MenuCategory::create([
            'name' => 'Desserts',
            'code' => 'DES',
            'active' => true,
        ]);

        $response = $this->post("/categories/{$cat->id}/toggle-active");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'active' => false]);

        $cat->refresh();
        $this->assertFalse($cat->active);
    }

    /**
     * Test deleting a category.
     */
    public function test_can_delete_category(): void
    {
        $cat = MenuCategory::create([
            'name' => 'Sides',
            'code' => 'SDE',
            'active' => true,
        ]);

        $response = $this->delete("/categories/{$cat->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('menu_categories', ['id' => $cat->id]);
    }
}
