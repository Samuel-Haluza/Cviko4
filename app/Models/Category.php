<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Get the notes for this category.
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_category');
    }

    /**
     * CUSTOM QUERY METHODS
     */

    /**
     * Get all categories ordered by name.
     */
    public static function getAllOrderedByName(): Builder
    {
        return static::query()->orderBy('name', 'asc');
    }

    /**
     * Check if category name already exists.
     */
    public static function nameExists(string $name): bool
    {
        return static::where('name', $name)->exists();
    }

    /**
     * Get note count for this category.
     */
    public function getNoteCount(): int
    {
        return $this->notes()->count();
    }

    /**
     * Get categories with note count using query builder.
     */
    public static function withNoteCount()
    {
        return static::query()
            ->withCount('notes')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'notes_count' => $category->notes_count,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });
    }

    /**
     * Search categories by name.
     */
    public static function searchByName(string $query)
    {
        return static::where('name', 'LIKE', '%' . $query . '%')
            ->orderBy('name', 'asc')
            ->get();
    }
}
