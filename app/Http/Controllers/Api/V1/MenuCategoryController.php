<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Menu\MenuCategoryRequest;
use App\Http\Requests\Api\V1\Menu\ReorderCategoriesRequest;
use App\Http\Resources\Api\V1\MenuCategoryResource;
use App\Models\MenuCategory;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuCategoryController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $categories = MenuCategory::where('business_id', $this->businessId($request))
            ->when($request->boolean('with_items'), fn ($query) => $query->with(['menuItems' => function ($items) {
                $items->where('status', 'active')
                    ->where('availability', true)
                    ->with(['variants', 'presetImage'])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }]))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 50));

        return $this->success(MenuCategoryResource::collection($categories), 'Categories');
    }

    public function store(MenuCategoryRequest $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $data = $request->validated();

        $this->assertUniqueName($businessId, $data['name']);
        $code = $this->generateCode($businessId, $data['name']);

        $category = MenuCategory::create([
            ...$data,
            'business_id' => $businessId,
            'code' => $code,
            'active' => $data['active'] ?? true,
            'status' => $data['status'] ?? 'active',
        ]);

        $this->auditLogService->record($request->user(), $businessId, 'category.created', $category);

        return $this->success(new MenuCategoryResource($category), 'Category created', 201);
    }

    public function show(Request $request, MenuCategory $category): JsonResponse
    {
        if ($category->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new MenuCategoryResource($category->load('menuItems.variants')), 'Category details');
    }

    public function update(MenuCategoryRequest $request, MenuCategory $category): JsonResponse
    {
        if ($category->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $data = $request->validated();

        if (isset($data['name']) && $data['name'] !== $category->name) {
            $this->assertUniqueName($category->business_id, $data['name'], $category->id);
            $data['code'] = $this->generateCode($category->business_id, $data['name'], $category->id);
        }

        $category->update($data);
        $this->auditLogService->record($request->user(), $category->business_id, 'category.updated', $category);

        return $this->success(new MenuCategoryResource($category->fresh()), 'Category updated');
    }

    public function destroy(Request $request, MenuCategory $category): JsonResponse
    {
        if ($category->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $category->update(['active' => false, 'status' => 'inactive']);
        $this->auditLogService->record($request->user(), $category->business_id, 'category.deactivated', $category);

        return $this->success(new MenuCategoryResource($category->fresh()), 'Category deactivated');
    }

    public function toggle(Request $request, MenuCategory $category): JsonResponse
    {
        if ($category->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        $active = ! $category->active;
        $category->update([
            'active' => $active,
            'status' => $active ? 'active' : 'inactive',
        ]);

        return $this->success(new MenuCategoryResource($category->fresh()), 'Category status updated');
    }

    public function reorder(ReorderCategoriesRequest $request): JsonResponse
    {
        $businessId = $this->businessId($request);

        DB::transaction(function () use ($request, $businessId) {
            foreach ($request->validated('categories') as $categoryData) {
                MenuCategory::where('business_id', $businessId)
                    ->whereKey($categoryData['id'])
                    ->update(['sort_order' => $categoryData['sort_order']]);
            }
        });

        return $this->success(null, 'Categories reordered');
    }

    private function assertUniqueName(int $businessId, string $name, ?int $ignoreId = null): void
    {
        $exists = MenuCategory::where('business_id', $businessId)
            ->where('name', $name)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['name' => ['The category name has already been taken.']]);
        }
    }

    private function generateCode(int $businessId, string $name, ?int $ignoreId = null): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)) ?: 'CAT';
        $code = $base;
        $suffix = 1;

        while (MenuCategory::where('business_id', $businessId)
            ->where('code', $code)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $code = $base.$suffix;
            $suffix++;
        }

        return $code;
    }
}
