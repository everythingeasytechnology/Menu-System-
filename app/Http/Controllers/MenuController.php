<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\PresetFoodImage;
use App\Services\MenuImageService;
use App\Services\OwnerDashboardService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuImageService $menuImageService,
        private readonly OwnerDashboardService $dashboardService,
    ) {}

    /**
     * Display a listing of the menu items with filters.
     */
    public function index(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $query = MenuItem::with(['variants', 'presetImage'])
            ->where('business_id', $business->id);

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%'.$search.'%');
        }

        // Apply Category Filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        $items = $query->get();

        // Retrieve all active categories from the categories table
        $categories = MenuCategory::where('business_id', $business->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return view('menu', compact('items', 'categories'));
    }

    /**
     * Store a newly created menu item with portion variants.
     */
    public function store(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'type' => 'required|string|in:veg,non-veg',
            'cooking_time' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preset_image_id' => 'nullable|integer|exists:preset_food_images,id',
            'variants' => 'required|array|min:1',
            'variants.*.label' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        $presetImageId = $this->menuImageService->resolvePresetImageId(
            $request,
            $validated['name'],
            $validated['category'],
        );

        $menuItem = MenuItem::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => $validated['type'],
            'cooking_time' => $validated['cooking_time'],
            'preset_food_image_id' => $presetImageId,
            'stock' => true,
        ]);

        foreach ($validated['variants'] as $variant) {
            $menuItem->variants()->create([
                'label' => $variant['label'],
                'price' => $variant['price'],
            ]);
        }

        return redirect()->back()->with('success', 'Menu Item added successfully!');
    }

    /**
     * Update the specified menu item with new portion variants.
     */
    public function update(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'type' => 'required|string|in:veg,non-veg',
            'cooking_time' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'preset_image_id' => 'nullable|integer|exists:preset_food_images,id',
            'remove_image' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*.label' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
        ]);

        $menuItem = MenuItem::where('business_id', $business->id)->findOrFail($id);

        $presetImageId = $this->menuImageService->resolvePresetImageId(
            $request,
            $validated['name'],
            $validated['category'],
            $menuItem->preset_food_image_id,
        );

        $menuItem->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => $validated['type'],
            'cooking_time' => $validated['cooking_time'],
            'preset_food_image_id' => $presetImageId,
        ]);

        // Delete existing variants and re-create them
        $menuItem->variants()->delete();

        foreach ($validated['variants'] as $variant) {
            $menuItem->variants()->create([
                'label' => $variant['label'],
                'price' => $variant['price'],
            ]);
        }

        return redirect()->back()->with('success', 'Menu Item updated successfully!');
    }

    /**
     * Toggle the stock availability status of a menu item.
     */
    public function toggleStock(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $menuItem = MenuItem::where('business_id', $business->id)->findOrFail($id);
        $menuItem->stock = ! $menuItem->stock;
        $menuItem->save();

        return response()->json([
            'success' => true,
            'stock' => $menuItem->stock,
        ]);
    }

    /**
     * Remove the specified menu item from the catalog.
     */
    public function destroy(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $menuItem = MenuItem::where('business_id', $business->id)->findOrFail($id);
        $menuItem->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get searchable preset food images.
     */
    public function presetImages(Request $request)
    {
        $query = PresetFoodImage::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('tags', 'like', '%'.$search.'%');
        }

        $presets = $query->take(15)->get();

        return response()->json($presets);
    }
}
