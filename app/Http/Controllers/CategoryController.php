<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categoriesWithParents = Category::with('parent')
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', ['categories' => $categoriesWithParents]);
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return response()->json(['message' => 'Category created successfully']);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validatedData = $request->validated();

        if ($validatedData['parent_id'] == $category->id) {
            return response()->json(['errors' => ['parent_id' => ['Category cannot be its own parent']]], 422);
        }

        if ($validatedData['parent_id']) {
            $proposedParentCategory = Category::find($validatedData['parent_id']);
            $currentAncestor = $proposedParentCategory;
            while ($currentAncestor) {
                if ($currentAncestor->id == $category->id) {
                    return response()->json(['errors' => ['parent_id' => ['Cannot assign a descendant as parent']]], 422);
                }
                $currentAncestor = $currentAncestor->parent;
            }
        }

        $category->update($validatedData);

        return response()->json(['message' => 'Category updated successfully']);
    }

    public function destroy(Category $category)
    {
        DB::transaction(function () use ($category) {
            $originalParentId = $category->getParentId();
            Category::where('parent_id', $category->id)
                ->update(['parent_id' => $originalParentId]);
            $category->delete();
        });

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
