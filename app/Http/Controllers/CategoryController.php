<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of all categories.
     * GET /api/categories
     */
    public function index()
    {
        $categories = DB::table('categories')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'categories' => $categories,
            'total' => count($categories)
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
        $exists = DB::table('categories')
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Kategória s týmto menom už existuje.'
            ], Response::HTTP_CONFLICT);
        }

        // Insert new category
        $id = DB::table('categories')->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Retrieve and return the created category
        $category = DB::table('categories')->find($id);

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
        $category = DB::table('categories')
            ->where('id', $id)
            ->first();

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
        // Check if category exists
        $category = DB::table('categories')
            ->where('id', $id)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validation - if name is provided and different
        if ($request->has('name') && $request->name !== $category->name) {
            $exists = DB::table('categories')
                ->where('name', $request->name)
                ->where('id', '<>', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Kategória s týmto menom už existuje.'
                ], Response::HTTP_CONFLICT);
            }
        }

        // Update category
        DB::table('categories')
            ->where('id', $id)
            ->update([
                'name' => $request->name ?? $category->name,
                'updated_at' => now(),
            ]);

        // Retrieve and return updated category
        $updatedCategory = DB::table('categories')->find($id);

        return response()->json([
            'message' => 'Kategória bola úspešne aktualizovaná.',
            'category' => $updatedCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified category from storage (Hard delete).
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id)
    {
        // Check if category exists
        $category = DB::table('categories')
            ->where('id', $id)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if category is used in any notes
        $usageCount = DB::table('note_category')
            ->where('category_id', $id)
            ->count();

        if ($usageCount > 0) {
            return response()->json([
                'message' => 'Kategória nemôže byť vymazaná, pretože je v súčasnosti priradená k '
                    . $usageCount . ' poznámkam.',
                'usage_count' => $usageCount
            ], Response::HTTP_CONFLICT);
        }

        // Delete category
        DB::table('categories')->where('id', $id)->delete();

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
        $categories = DB::table('categories')
            ->leftJoin('note_category', 'categories.id', '=', 'note_category.category_id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(note_category.id) as notes_count'),
                'categories.created_at',
                'categories.updated_at'
            )
            ->groupBy('categories.id', 'categories.name', 'categories.created_at', 'categories.updated_at')
            ->orderBy('categories.name', 'asc')
            ->get();

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

        $categories = DB::table('categories')
            ->where('name', 'LIKE', '%' . $query . '%')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'search_query' => $query,
            'categories' => $categories,
            'total' => count($categories)
        ], Response::HTTP_OK);
    }
}
