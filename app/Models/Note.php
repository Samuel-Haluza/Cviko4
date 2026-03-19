<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'notes';

    protected $primaryKey = 'id';

    //public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the user that owns this note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for this note.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'note_category');
    }

    /**
     * CUSTOM QUERY METHODS
     */

    /**
     * Pin this note.
     */
    public function pin(): bool
    {
        return $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin this note.
     */
    public function unpin(): bool
    {
        return $this->update(['is_pinned' => false]);
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(): bool
    {
        return $this->update(['is_pinned' => !$this->is_pinned]);
    }

    /**
     * Change status to published.
     */
    public function publish(): bool
    {
        return $this->update(['status' => 'published']);
    }

    /**
     * Change status to archived.
     */
    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    /**
     * Change status to draft.
     */
    public function draft(): bool
    {
        return $this->update(['status' => 'draft']);
    }

    /**
     * Change note status.
     */
    public function changeStatus(string $status): bool
    {
        if (!in_array($status, ['draft', 'archived', 'published'])) {
            return false;
        }
        return $this->update(['status' => $status]);
    }

    /**
     * Get all pinned notes.
     */
    public static function getPinned()
    {
        return static::where('is_pinned', true)
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Get notes by status.
     */
    public static function getByStatus(string $status)
    {
        return static::where('status', $status)
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Get statistics by status.
     */
    public static function getStatsByStatus()
    {
        return static::query()
            ->whereNull('deleted_at')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
    }

    /**
     * Archive old draft notes (older than 30 days).
     */
    public static function archiveOldDrafts(): int
    {
        return static::where('status', 'draft')
            ->where('updated_at', '<', now()->subDays(30))
            ->whereNull('deleted_at')
            ->update(['status' => 'archived']);
    }

    /**
     * Search notes by title or body.
     */
    public static function search(string $query)
    {
        return static::where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('body', 'LIKE', '%' . $query . '%')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Get user notes with categories.
     */
    public static function getUserNotesWithCategories(string $userId)
    {
        return static::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->with('categories')
            ->orderByDesc('updated_at')
            ->get();
    }
}