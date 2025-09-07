<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categories.view')->only(['index', 'show']);
        $this->middleware('permission:categories.create')->only(['create', 'store']);
        $this->middleware('permission:categories.update')->only(['edit', 'update']);
        $this->middleware('permission:categories.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $query = Category::withCount(['products', 'children'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($request->parent_only === 'true', function ($q) {
                $q->whereNull('parent_id');
            });

        $categories = $query->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_categories' => Category::count(),
            'active_categories' => Category::where('is_active', true)->count(),
            'parent_categories' => Category::whereNull('parent_id')->count(),
            'subcategories' => Category::whereNotNull('parent_id')->count(),
        ];

        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'stats' => $stats,
            'parent_categories' => $parentCategories,
            'filters' => $request->only(['search', 'status', 'parent_only']),
        ]);
    }

    public function create(): Response
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Create', [
            'parent_categories' => $parentCategories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        activity()
            ->performedOn($category)
            ->causedBy(auth()->user())
            ->log('Category created');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): Response
    {
        $category->load(['parent', 'children.products', 'products']);

        $stats = [
            'total_products' => $category->products()->count(),
            'active_products' => $category->products()->where('is_active', true)->count(),
            'subcategories' => $category->children()->count(),
            'total_sales' => $this->getCategorySales($category),
        ];

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
            'stats' => $stats,
        ]);
    }

    public function edit(Category $category): Response
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parent_categories' => $parentCategories,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        // Prevent circular reference
        if ($request->parent_id && $this->wouldCreateCircularReference($category, $request->parent_id)) {
            return redirect()->back()->withErrors(['parent_id' => 'Cannot set parent category as it would create a circular reference.']);
        }

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        activity()
            ->performedOn($category)
            ->causedBy(auth()->user())
            ->log('Category updated');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()->withErrors(['category' => 'Cannot delete category with products. Please move products to another category first.']);
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return redirect()->back()->withErrors(['category' => 'Cannot delete category with subcategories. Please delete subcategories first.']);
        }

        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $categoryName = $category->name;
        $category->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['name' => $categoryName])
            ->log('Category deleted');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
        ]);

        $categories = Category::whereIn('id', $request->category_ids);
        $count = 0;

        switch ($request->action) {
            case 'activate':
                $count = $categories->update(['is_active' => true]);
                $message = "{$count} categories activated successfully.";
                break;

            case 'deactivate':
                $count = $categories->update(['is_active' => false]);
                $message = "{$count} categories deactivated successfully.";
                break;

            case 'feature':
                $count = $categories->update(['is_featured' => true]);
                $message = "{$count} categories marked as featured.";
                break;

            case 'unfeature':
                $count = $categories->update(['is_featured' => false]);
                $message = "{$count} categories unmarked as featured.";
                break;

            case 'delete':
                $categoriesToDelete = $categories->whereDoesntHave('products')
                    ->whereDoesntHave('children')
                    ->get();
                    
                foreach ($categoriesToDelete as $category) {
                    if ($category->image) {
                        Storage::disk('public')->delete($category->image);
                    }
                    $category->delete();
                }
                $count = $categoriesToDelete->count();
                $message = "{$count} categories deleted successfully.";
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return redirect()->back()->with('success', 'Categories reordered successfully.');
    }

    public function getTree(): Response
    {
        $categories = Category::with('children.children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Tree', [
            'categories' => $categories,
        ]);
    }

    private function wouldCreateCircularReference(Category $category, int $parentId): bool
    {
        $parent = Category::find($parentId);
        
        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }

    private function getCategorySales(Category $category): float
    {
        // Get sales for this category and all its subcategories
        $categoryIds = [$category->id];
        $this->addSubcategoryIds($category, $categoryIds);

        return \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('products.category_id', $categoryIds)
            ->where('orders.payment_status', 'paid')
            ->sum('order_items.total_price');
    }

    private function addSubcategoryIds(Category $category, array &$categoryIds): void
    {
        foreach ($category->children as $child) {
            $categoryIds[] = $child->id;
            $this->addSubcategoryIds($child, $categoryIds);
        }
    }
}