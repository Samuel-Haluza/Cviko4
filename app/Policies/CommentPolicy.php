<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view comments for a note.
     */
    public function viewForNote(User $user, Note $note): bool
    {
        if (in_array($note->status, ['published', 'archived'], true)) {
            return true;
        }

        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can view comments for a task.
     */
    public function viewForTask(User $user, Task $task): bool
    {
        if (!$task->note) {
            return false;
        }

        return $this->viewForNote($user, $task->note);
    }

    /**
     * Determine whether the user can create a comment for a note.
     */
    public function createForNote(User $user, Note $note): bool
    {
        return $this->viewForNote($user, $note);
    }

    /**
     * Determine whether the user can create a comment for a task.
     */
    public function createForTask(User $user, Task $task): bool
    {
        return $this->viewForTask($user, $task);
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isAdmin();
    }
}
