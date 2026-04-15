<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view tasks for a specific note.
     */
    public function viewForNote(User $user, Note $note): bool
    {
        if (in_array($note->status, ['published', 'archived'], true)) {
            return true;
        }

        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if (!$task->note) {
            return false;
        }

        if (in_array($task->note->status, ['published', 'archived'], true)) {
            return true;
        }

        return $task->note->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->note && $task->note->user_id === $user->id;
    }

    /**
     * Determine whether the user can toggle task completion.
     */
    public function toggle(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can mark task as complete.
     */
    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can mark task as incomplete.
     */
    public function incomplete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->note && $task->note->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
