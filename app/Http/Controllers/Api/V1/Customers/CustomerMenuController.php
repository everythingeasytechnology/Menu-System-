<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\MenuCategoryResource;
use App\Http\Resources\Api\V1\MenuItemResource;
use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\Customers\ScannerContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerMenuController extends ApiController
{
    public function __construct(private readonly ScannerContextResolver $scannerContextResolver)
    {
    }

    public function menu(string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        return $this->success([
            'business' => $this->businessPayload($business),
            'context' => $context,
            'categories' => MenuCategoryResource::collection($this->categoryQuery($business)->with([
                'menuItems' => fn ($items) => $this->availableMenuItemsQuery($business, $items),
            ])->get()),
        ], 'Customer menu');
    }

    public function categories(string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        return $this->success([
            'business' => $this->businessPayload($business),
            'context' => $context,
            'categories' => MenuCategoryResource::collection($this->categoryQuery($business)->get()),
        ], 'Customer categories');
    }

    public function category(string $qr, MenuCategory $category): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        if ($category->business_id !== $business->id || ! $category->active || $category->status !== 'active') {
            return $this->error('Resource not found', 404);
        }

        $category->load(['menuItems' => fn ($items) => $this->availableMenuItemsQuery($business, $items)]);

        return $this->success([
            'business' => $this->businessPayload($business),
            'context' => $context,
            'category' => new MenuCategoryResource($category),
        ], 'Customer category');
    }

    public function items(Request $request, string $qr): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:menu_categories,id'],
            'category' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:veg,non-veg'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $items = $this->availableMenuItemsQuery($business, MenuItem::query())
            ->when($request->filled('category_id'), fn ($query) => $query->where('menu_category_id', $request->integer('category_id')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->get();

        return $this->success([
            'business' => $this->businessPayload($business),
            'context' => $context,
            'items' => MenuItemResource::collection($items),
        ], 'Customer menu items');
    }

    public function item(string $qr, MenuItem $menuItem): JsonResponse
    {
        [$business, $context] = $this->scannerContextResolver->resolve($qr);

        if (
            $menuItem->business_id !== $business->id
            || $menuItem->status !== 'active'
            || ! $menuItem->availability
            || ! $menuItem->stock
        ) {
            return $this->error('Resource not found', 404);
        }

        return $this->success([
            'business' => $this->businessPayload($business),
            'context' => $context,
            'item' => new MenuItemResource($menuItem->load(['variants', 'presetImage', 'menuCategory'])),
        ], 'Customer menu item');
    }

    private function categoryQuery(Business $business)
    {
        return $business->categories()
            ->where('active', true)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    private function availableMenuItemsQuery(Business $business, $query)
    {
        return $query->where('business_id', $business->id)
            ->where('status', 'active')
            ->where('availability', true)
            ->where('stock', true)
            ->with(['variants', 'presetImage', 'menuCategory'])
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    private function businessPayload(Business $business): array
    {
        $settings = $business->businessSetting;
        $logoPath = $settings?->logo_path ?: $business->logo_path;

        return [
            'id' => $business->id,
            'name' => $settings?->brand_name ?: $business->name,
            'business_name' => $business->name,
            'type' => $business->type,
            'phone' => $business->phone,
            'email' => $business->email,
            'address' => $settings?->address ?: $business->address,
            'city' => $business->city,
            'state' => $settings?->state ?: $business->state,
            'country' => $settings?->country ?: $business->country,
            'logo_url' => $this->assetUrl($logoPath),
            'timezone' => $business->timezone,
            'gst_enabled' => (bool) ($settings?->gst_enabled ?? false),
            'cgst' => (float) ($settings?->cgst ?? 0),
            'sgst' => (float) ($settings?->sgst ?? 0),
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }
}
