<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\CategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $categories = QueryBuilder::for(Category::class)
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $userId);
            })
            ->allowedFilters(['type'])
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'type' => $request->type,
            'bucket' => $request->bucket,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_default' => false,
        ]);

        return $this->created(new CategoryResource($category));
    }

    public function show(Category $category, Request $request): JsonResponse
    {
        if ($category->user_id !== null && $category->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        return $this->success(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        if ($category->user_id === null) {
            return $this->error(
                ErrorCode::DEFAULT_READONLY,
                'Kategori default tidak dapat diubah.',
                [],
                403
            );
        }

        if ($category->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        $category->update($request->validated());

        return $this->success(new CategoryResource($category));
    }

    public function destroy(Category $category, Request $request): JsonResponse
    {
        if ($category->user_id === null) {
            return $this->error(
                ErrorCode::DEFAULT_READONLY,
                'Kategori default tidak dapat dihapus.',
                [],
                403
            );
        }

        if ($category->user_id !== $request->user()->id) {
            return $this->error(ErrorCode::FORBIDDEN, 'Akses ditolak', [], 403);
        }

        // Check if category is used
        $isUsed = Expense::where('category_id', $category->id)->exists();
        
        // Also check if it's used in incomes receipt if needed, but the spec says: "dipakai expense"
        
        $reassignToId = $request->query('reassign_to');

        if ($isUsed && !$reassignToId) {
            return $this->error(
                ErrorCode::IN_USE,
                'Kategori masih digunakan. Sertakan ?reassign_to=UUID untuk memindahkan transaksi.',
                [],
                409
            );
        }

        if ($reassignToId) {
            // Verify reassign category exists and belongs to user or is default
            $newCategory = Category::where(function ($query) use ($request) {
                    $query->whereNull('user_id')
                          ->orWhere('user_id', $request->user()->id);
                })
                ->where('id', $reassignToId)
                ->where('type', $category->type) // Should be same type ideally
                ->first();

            if (!$newCategory) {
                return $this->error(
                    ErrorCode::NOT_FOUND,
                    'Kategori pengganti tidak valid atau tidak ditemukan.',
                    [],
                    404
                );
            }

            DB::transaction(function () use ($category, $newCategory) {
                Expense::where('category_id', $category->id)
                       ->update(['category_id' => $newCategory->id]);
                
                $category->delete();
            });
        } else {
            $category->delete();
        }

        return $this->noContent();
    }
}
