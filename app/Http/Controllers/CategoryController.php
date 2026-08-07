<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with item counts.
     */
    public function index()
    {
        $categories = MenuCategory::all()->map(function ($cat) {
            $cat->count = MenuItem::where('category', $cat->name)->count();
            return $cat;
        });

        return view('categories', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_categories,name',
        ]);

        $name = $validated['name'];
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        if (empty($code)) {
            $code = 'CAT';
        }
        $originalCode = $code;
        $i = 1;
        while (MenuCategory::where('code', $code)->exists()) {
            $code = $originalCode . $i;
            $i++;
        }

        MenuCategory::create([
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
        $category = MenuCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_categories,name,' . $category->id,
        ]);

        $oldName = $category->name;
        $newName = $validated['name'];

        if ($oldName !== $newName) {
            // Update category name inside all MenuItems to preserve association
            MenuItem::where('category', $oldName)->update(['category' => $newName]);
        }

        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $newName), 0, 3));
        if (empty($code)) {
            $code = 'CAT';
        }
        $originalCode = $code;
        $i = 1;
        while (MenuCategory::where('code', $code)->where('id', '!=', $category->id)->exists()) {
            $code = $originalCode . $i;
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
    public function toggleActive($id)
    {
        $category = MenuCategory::findOrFail($id);
        $category->active = !$category->active;
        $category->save();

        return response()->json([
            'success' => true,
            'active' => $category->active,
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = MenuCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
