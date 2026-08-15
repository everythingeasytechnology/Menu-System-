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
    private const ITEM_RESULT_LIMIT = 80;

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
        $items = $this->menuItemsQuery($request, $business->id)
            ->limit(self::ITEM_RESULT_LIMIT)
            ->get();

        // Retrieve all active categories from the categories table
        $categories = MenuCategory::where('business_id', $business->id)
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return view('menu', compact('items', 'categories'));
    }

    public function items(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $items = $this->menuItemsQuery($request, $business->id)
            ->limit(self::ITEM_RESULT_LIMIT)
            ->get();

        return response()->json([
            'items' => $items,
            'limit' => self::ITEM_RESULT_LIMIT,
        ]);
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

        $categoryId = $this->resolveCategoryId($business->id, $validated['category']);

        if (! $categoryId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Select a valid, active category before saving this item.']);
        }

        $presetImageId = $this->menuImageService->resolvePresetImageId(
            $request,
            $validated['name'],
            $validated['category'],
        );

        $menuItem = MenuItem::create([
            'business_id' => $business->id,
            'menu_category_id' => $categoryId,
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

        $categoryId = $this->resolveCategoryId($business->id, $validated['category']);

        if (! $categoryId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'Select a valid, active category before saving this item.']);
        }

        $presetImageId = $this->menuImageService->resolvePresetImageId(
            $request,
            $validated['name'],
            $validated['category'],
            $menuItem->preset_food_image_id,
        );

        $menuItem->update([
            'menu_category_id' => $categoryId,
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

    private function menuItemsQuery(Request $request, int $businessId)
    {
        return MenuItem::with(['variants', 'presetImage'])
            ->where('business_id', $businessId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('category') && $request->input('category') !== 'all', function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            })
            ->orderBy('name');
    }

    /**
     * Look up the MenuCategory matching the legacy category name so items
     * created/edited from this form stay linked to it — the customer-facing
     * QR menu reads menu_category_id, not the legacy category string.
     */
    private function resolveCategoryId(int $businessId, string $categoryName): ?int
    {
        return MenuCategory::where('business_id', $businessId)
            ->where('name', $categoryName)
            ->value('id');
    }
}
