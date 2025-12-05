<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Services\CategoryService;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->get('paginate', false)) {
            $query = Category::with([
                'parent.parent',
                'children.children',
            ]);

            if ($request->get('search', false)) {
                $query->where('name', 'like', '%' . $request->get('search') . '%');
            }

            if ($request->get('parents', false)) {
                $query->whereNull('parent_id');
            }

            if ($request->get('where_has_children', false)) {
                $query->whereHas('children');
            }

            $category_ids = auth()->user()->role->getCategoryIds();
            if (count($category_ids) > 0) {
                $query->whereIn('id', $category_ids);
            }

            $categories = $query
                ->orderBy('updated_at', 'desc')
                ->paginate($request->get('per_page', 10));

            return response()->json([
                'categories' => $categories,
            ]);
        }
        $categories = $this->categoryService->getAll($request);
        return $this->success('Success', CategoryResource::collection($categories));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): \Illuminate\Http\JsonResponse
    {
        $category = $this->categoryService->store($request);
        return $this->success('Category created successfully', new CategoryResource($category));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): \Illuminate\Http\JsonResponse
    {
        return $this->success('Success', new CategoryResource($category));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): \Illuminate\Http\JsonResponse
    {
        $category = $this->categoryService->update($request, $category);
        return $this->success('Category updated successfully', new CategoryResource($category));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): \Illuminate\Http\JsonResponse
    {
        $category->delete();
        return $this->success('Category deleted successfully');
    }
}
