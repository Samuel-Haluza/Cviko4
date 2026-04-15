<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;

class AttachmentPolicy
{
    public function viewForNote(User $user, Note $note): bool
    {
        if (in_array($note->status, ['published', 'archived'], true)) {
            return true;
        }

        return $note->user_id === $user->id || $user->isAdmin();
    }

    public function createForNote(User $user, Note $note): bool
    {
        return $note->user_id === $user->id || $user->isAdmin();
    }

    public function view(User $user, Attachment $attachment): bool
    {
        if ($attachment->visibility === 'public') {
            return true;
        }

        if ($attachment->attachable_type === Note::class && $attachment->attachable) {
            return $attachment->attachable->user_id === $user->id || $user->isAdmin();
        }

        if ($attachment->attachable_type === Task::class && $attachment->attachable && $attachment->attachable->note) {
            return $attachment->attachable->note->user_id === $user->id || $user->isAdmin();
        }

        if ($attachment->attachable_type === User::class && $attachment->attachable) {
            return $attachment->attachable->id === $user->id || $user->isAdmin();
        }

        return false;
    }

    public function link(User $user, Attachment $attachment): bool
    {
        return $this->view($user, $attachment);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if ($attachment->attachable_type === Note::class && $attachment->attachable) {
            return $attachment->attachable->user_id === $user->id || $user->isAdmin();
        }

        return false;
    }
}
