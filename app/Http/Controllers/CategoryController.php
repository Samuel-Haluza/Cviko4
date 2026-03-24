<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

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
     * 
     * Request body:
     * {
     *   "name": "string (required, unique)",
     *   "color": "string (optional, hex color code)"
     * }
     */
    public function store(Request $request)
    {
        // Validácia vstupných dát
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:64',
                Rule::unique('categories', 'name'),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
        ], [
            'name.required' => 'Pole "name" je povinné.',
            'name.min' => 'Názov kategórie musí mať minimálne 2 znaky.',
            'name.max' => 'Názov kategórie nesmie dlhší ako 64 znakov.',
            'name.unique' => 'Kategória s týmto menom už existuje.',
            'color.regex' => 'Farba musí byť v hex formáte (napr. #FF5733 alebo #FFF).',
        ]);

        // Vytvorenie novej kategórie
        $category = Category::create($validated);

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
     * 
     * Request body:
     * {
     *   "name": "string (optional)",
     *   "color": "string (optional)"
     * }
     */
    public function update(Request $request, string $id)
    {
        // Ziskaj kategóriu
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validácia vstupných dát
        // Rule::unique()->ignore() umožňuje ponechať pôvodný name
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:64',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
        ], [
            'name.required' => 'Pole "name" je povinné.',
            'name.min' => 'Názov kategórie musí mať minimálne 2 znaky.',
            'name.max' => 'Názov kategórie nesmie dlhší ako 64 znakov.',
            'name.unique' => 'Kategória s týmto menom už existuje.',
            'color.regex' => 'Farba musí byť v hex formáte (napr. #FF5733 alebo #FFF).',
        ]);

        // Aktualizuj kategóriu
        $category->update($validated);

        return response()->json([
            'message' => 'Kategória bola úspešne aktualizovaná.',
            'category' => $category->fresh()
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
