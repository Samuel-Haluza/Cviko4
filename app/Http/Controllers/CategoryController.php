<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of all categories.
     * GET /api/categories
     */
    public function index()
    {
        $categories = Category::getAllOrderedByName();

        return response()->json([
            'categories' => $categories,
            'total' => $categories->count()
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created category in storage.
     * POST /api/categories
     */
    public function store(Request $request)
    {
        // Validation
        if (!$request->has('name') || empty($request->name)) {
            return response()->json([
                'message' => 'Pole "name" je povinné.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if category already exists
        if (Category::nameExists($request->name)) {
            return response()->json([
                'message' => 'Kategória s týmto menom už existuje.'
            ], Response::HTTP_CONFLICT);
        }

        // Create new category
        $category = Category::create([
            'name' => $request->name,
            'color' => $request->color ?? null,
        ]);

        return response()->json([
            'message' => 'Kategória bola úspešne vytvorená.',
            'category' => $category
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified category.
     * GET /api/categories/{id}
     */
    public function show(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'category' => $category
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified category in storage.
     * PUT/PATCH /api/categories/{id}
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validation - if name is provided and different
        if ($request->has('name') && $request->name !== $category->name) {
            if (Category::where('name', $request->name)->where('id', '<>', $id)->exists()) {
                return response()->json([
                    'message' => 'Kategória s týmto menom už existuje.'
                ], Response::HTTP_CONFLICT);
            }
        }

        // Update category
        $category->update([
            'name' => $request->name ?? $category->name,
            'color' => $request->color ?? $category->color,
        ]);

        return response()->json([
            'message' => 'Kategória bola úspešne aktualizovaná.',
            'category' => $category
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified category from storage (Hard delete).
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if category is used in any notes
        $usageCount = $category->getNoteCount();

        if ($usageCount > 0) {
            return response()->json([
                'message' => 'Kategória nemôže byť vymazaná, pretože je v súčasnosti priradená k '
                    . $usageCount . ' poznámkam.',
                'usage_count' => $usageCount
            ], Response::HTTP_CONFLICT);
        }

        // Delete category
        $category->delete();

        return response()->json([
            'message' => 'Kategória bola úspešne vymazaná.'
        ], Response::HTTP_OK);
    }

    /**
     * Get categories with note count.
     * GET /api/categories-with-count
     */
    public function getCategoriesWithCount()
    {
        $categories = Category::withNoteCount();

        return response()->json([
            'categories' => $categories
        ], Response::HTTP_OK);
    }

    /**
     * Search categories by name.
     * GET /api/categories-search?q=search_term
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (empty($query)) {
            return response()->json([
                'message' => 'Vyhľadávací parameter "q" je povinný.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $categories = Category::searchByName($query);

        return response()->json([
            'search_query' => $query,
            'categories' => $categories,
            'total' => $categories->count()
        ], Response::HTTP_OK);
    }
}
