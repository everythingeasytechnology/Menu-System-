<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Menu\MenuItemRequest;
use App\Http\Resources\Api\V1\MenuItemResource;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuItemController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->filteredQuery($request)
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(MenuItemResource::collection($items), 'Menu items');
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:veg,non-veg'],
            'category_id' => ['nullable', 'integer', 'exists:menu_categories,id'],
            'category' => ['nullable', 'string', 'max:255'],
            'available' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $items = $this->filteredQuery($request)
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(MenuItemResource::collection($items), 'Filtered menu items');
    }

    public function store(MenuItemRequest $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $data = $request->validated();
        $category = $this->categoryForBusiness($businessId, $data['category_id']);

        $item = DB::transaction(function () use ($businessId, $data, $category, $request) {
            $variants = $data['variants'] ?? [];
            unset($data['variants'], $data['category_id']);

            $item = MenuItem::create([
                ...$data,
                'business_id' => $businessId,
                'menu_category_id' => $category->id,
                'category' => $category->name,
                'type' => $data['type'] ?? 'veg',
                'stock' => $data['stock'] ?? true,
                'availability' => $data['availability'] ?? true,
                'status' => $data['status'] ?? 'active',
            ]);

            foreach ($variants as $variant) {
                $item->variants()->create([
                    'label' => $variant['label'],
                    'price' => $variant['price'],
                ]);
            }

            $this->auditLogService->record($request->user(), $businessId, 'menu_item.created', $item);

            return $item->load(['variants', 'presetImage']);
        });

        return $this->success(new MenuItemResource($item), 'Menu item created', 201);
    }

    public function show(Request $request, MenuItem $menuItem): JsonResponse
    {
        if ($menuItem->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new MenuItemResource($menuItem->load(['variants', 'presetImage'])), 'Menu item details');
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        if ($menuItem->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $data = $request->validated();

        $item = DB::transaction(function () use ($data, $menuItem, $request) {
            $variants = $data['variants'] ?? null;

            if (isset($data['category_id'])) {
                $category = $this->categoryForBusiness($menuItem->business_id, $data['category_id']);
                $data['menu_category_id'] = $category->id;
                $data['category'] = $category->name;
                unset($data['category_id']);
            }

            unset($data['variants']);
            $menuItem->update($data);

            if (is_array($variants)) {
                $menuItem->variants()->delete();

                foreach ($variants as $variant) {
                    $menuItem->variants()->create([
                        'label' => $variant['label'],
                        'price' => $variant['price'],
                    ]);
                }
            }

            $this->auditLogService->record($request->user(), $menuItem->business_id, 'menu_item.updated', $menuItem);

            return $menuItem->fresh(['variants', 'presetImage']);
        });

        return $this->success(new MenuItemResource($item), 'Menu item updated');
    }

    public function destroy(Request $request, MenuItem $menuItem): JsonResponse
    {
        if ($menuItem->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $menuItem->update(['status' => 'inactive', 'availability' => false, 'stock' => false]);
        $this->auditLogService->record($request->user(), $menuItem->business_id, 'menu_item.deactivated', $menuItem);

        return $this->success(new MenuItemResource($menuItem->fresh(['variants', 'presetImage'])), 'Menu item deactivated');
    }

    public function toggleAvailability(Request $request, MenuItem $menuItem): JsonResponse
    {
        if ($menuItem->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $available = ! ($menuItem->availability && $menuItem->stock);
        $menuItem->update(['availability' => $available, 'stock' => $available]);

        return $this->success(new MenuItemResource($menuItem->fresh(['variants', 'presetImage'])), 'Menu item availability updated');
    }

    private function categoryForBusiness(int $businessId, int $categoryId): MenuCategory
    {
        $category = MenuCategory::where('business_id', $businessId)->whereKey($categoryId)->first();

        if (! $category) {
            throw ValidationException::withMessages(['category_id' => ['Category does not belong to this business.']]);
        }

        return $category;
    }

    private function filteredQuery(Request $request)
    {
        return MenuItem::with(['variants', 'presetImage'])
            ->where('business_id', $this->businessId($request))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('menu_category_id', $request->integer('category_id')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('available'), fn ($query) => $query->where('availability', $request->boolean('available'))->where('stock', $request->boolean('available')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
