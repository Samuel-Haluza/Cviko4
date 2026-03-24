<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'comments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'note_id',
        'task_id',
        'title',
        'body',
    ];

    /**
     * Get the user that owns this comment.
     * Komentár patrí jednému userovi (Many-To-One)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the note that owns this comment.
     * Komentár môže patriť poznámke (Many-To-One)
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the task that owns this comment.
     * Komentár môže patriť úlohe (Many-To-One)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
