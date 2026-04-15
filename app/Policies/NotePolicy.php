<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        if ($note->status === 'published' || $note->status === 'archived') {
            return true;
        }

        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view notes of a specific user.
     */
    public function viewForUser(User $user, string $targetUserId): bool
    {
        return $user->id === (int) $targetUserId || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can pin the model.
     */
    public function pin(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can unpin the model.
     */
    public function unpin(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can toggle pin status.
     */
    public function togglePin(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can move the model to draft.
     */
    public function draft(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }

    /**
     * Determine whether the user can view notes by status.
     */
    public function viewByStatus(User $user, string $status): bool
    {
        if ($status === 'draft') {
            return $user->isAdmin();
        }

        return true;
    }

    /**
     * Determine whether the user can archive old drafts globally.
     */
    public function bulkArchiveDrafts(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return false;
    }
}
