<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'include_children' => 'nullable|boolean',
            'active_only' => 'nullable|boolean',
        ]);

        $query = Category::select(['id', 'name', 'slug', 'description', 'image', 'parent_id', 'sort_order']);

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->rootCategories();
        }

        if ($request->boolean('include_children')) {
            $query->with(['children' => function ($query) use ($request) {
                if ($request->boolean('active_only', true)) {
                    $query->active();
                }
                $query->orderBy('sort_order');
            }]);
        }

        $categories = $query->orderBy('sort_order')->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $category->load([
            'parent:id,name,slug',
            'children' => function ($query) {
                $query->active()->orderBy('sort_order');
            },
        ]);

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Get category tree.
     */
    public function tree()
    {
        $categories = Category::active()
            ->rootCategories()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Search categories.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $categories = Category::select(['id', 'name', 'slug', 'description', 'parent_id'])
            ->active()
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%')
            ->with('parent:id,name')
            ->take($request->get('limit', 20))
            ->get();

        return response()->json([
            'data' => $categories,
            'meta' => [
                'query' => $request->q,
                'total' => $categories->count(),
            ],
        ]);
    }
}