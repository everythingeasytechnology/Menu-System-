<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Display a listing of the menu items with filters.
     */
    public function index(Request $request)
    {
        $query = MenuItem::with(['variants', 'presetImage']);

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Apply Category Filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        $items = $query->get();

        // Dynamically get all categories currently stored in database for the filter tabs
        $categories = MenuItem::select('category')->distinct()->pluck('category')->toArray();

        return view('menu', compact('items', 'categories'));
    }

    /**
     * Store a newly created menu item with portion variants.
     */
    public function store(Request $request)
    {
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

        $presetImageId = null;
        if ($request->hasFile('image')) {
            // Store and compress custom image
            $path = $this->compressAndStoreImage($request->file('image'));
            // Save in preset library so everyone can use it
            $preset = \App\Models\PresetFoodImage::create([
                'name' => $validated['name'],
                'tags' => strtolower($validated['name'] . ', ' . $validated['category']),
                'image_path' => 'storage/' . $path,
            ]);
            $presetImageId = $preset->id;
        } elseif ($request->filled('preset_image_id')) {
            $presetImageId = $request->input('preset_image_id');
        }

        $menuItem = MenuItem::create([
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

        $menuItem = MenuItem::findOrFail($id);

        $presetImageId = $menuItem->preset_food_image_id;

        if ($request->hasFile('image')) {
            $path = $this->compressAndStoreImage($request->file('image'));
            $preset = \App\Models\PresetFoodImage::create([
                'name' => $validated['name'],
                'tags' => strtolower($validated['name'] . ', ' . $validated['category']),
                'image_path' => 'storage/' . $path,
            ]);
            $presetImageId = $preset->id;
        } elseif ($request->filled('preset_image_id')) {
            $presetImageId = $request->input('preset_image_id');
        } elseif ($request->input('remove_image') === '1') {
            $presetImageId = null;
        }
        
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
    public function toggleStock($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->stock = !$menuItem->stock;
        $menuItem->save();

        return response()->json([
            'success' => true,
            'stock' => $menuItem->stock,
        ]);
    }

    /**
     * Remove the specified menu item from the catalog.
     */
    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
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
        $query = \App\Models\PresetFoodImage::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('tags', 'like', '%' . $search . '%');
        }

        $presets = $query->take(15)->get();

        return response()->json($presets);
    }

    /**
     * Compress uploaded image using GD library to optimize size without quality loss.
     */
    private function compressAndStoreImage($file)
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $directory = storage_path('app/public/menu_items');
        
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $destinationPath = $directory . '/' . $filename;
        $sourcePath = $file->getRealPath();

        $info = @getimagesize($sourcePath);
        if (!$info) {
            return $file->store('menu_items', 'public');
        }

        $mime = $info['mime'];

        try {
            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $image = @imagecreatefromjpeg($sourcePath);
                if ($image) {
                    @imagejpeg($image, $destinationPath, 80); // Compress with 80% quality (no human-visible quality loss)
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } elseif ($mime === 'image/png') {
                $image = @imagecreatefrompng($sourcePath);
                if ($image) {
                    @imagealphablending($image, false);
                    @imagesavealpha($image, true);
                    @imagepng($image, $destinationPath, 6); // Compress PNG losslessly
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } elseif ($mime === 'image/gif') {
                $image = @imagecreatefromgif($sourcePath);
                if ($image) {
                    @imagegif($image, $destinationPath);
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } else {
                copy($sourcePath, $destinationPath);
            }
        } catch (\Exception $e) {
            copy($sourcePath, $destinationPath);
        }

        return 'menu_items/' . $filename;
    }
}
