<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\OwnerDashboardService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct(private readonly OwnerDashboardService $dashboardService) {}

    /**
     * Display a listing of categories with item counts.
     */
    public function index(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());

        $categories = MenuCategory::where('business_id', $business->id)->get()->map(function ($cat) use ($business) {
            $cat->count = MenuItem::where('business_id', $business->id)
                ->where('category', $cat->name)
                ->count();

            return $cat;
        });

        return view('categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_categories', 'name')->where('business_id', $business->id),
            ],
        ]);

        $name = $validated['name'];
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        if (empty($code)) {
            $code = 'CAT';
        }
        $originalCode = $code;
        $i = 1;
        while (MenuCategory::where('business_id', $business->id)->where('code', $code)->exists()) {
            $code = $originalCode.$i;
            $i++;
        }

        MenuCategory::create([
            'business_id' => $business->id,
            'name' => $name,
            'code' => $code,
            'active' => true,
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $category = MenuCategory::where('business_id', $business->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_categories', 'name')
                    ->where('business_id', $business->id)
                    ->ignore($category->id),
            ],
        ]);

        $oldName = $category->name;
        $newName = $validated['name'];

        if ($oldName !== $newName) {
            // Update category name inside all MenuItems to preserve association
            MenuItem::where('business_id', $business->id)
                ->where('category', $oldName)
                ->update(['category' => $newName]);
        }

        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $newName), 0, 3));
        if (empty($code)) {
            $code = 'CAT';
        }
        $originalCode = $code;
        $i = 1;
        while (MenuCategory::where('business_id', $business->id)
            ->where('code', $code)
            ->where('id', '!=', $category->id)
            ->exists()) {
            $code = $originalCode.$i;
            $i++;
        }

        $category->update([
            'name' => $newName,
            'code' => $code,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    /**
     * Toggle active state of category.
     */
    public function toggleActive(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $category = MenuCategory::where('business_id', $business->id)->findOrFail($id);
        $category->active = ! $category->active;
        $category->save();

        return response()->json([
            'success' => true,
            'active' => $category->active,
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, $id)
    {
        $business = $this->dashboardService->businessFor($request->user());
        $category = MenuCategory::where('business_id', $business->id)->findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
