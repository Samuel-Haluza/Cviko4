<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * Get comments for a note.
     * GET /api/notes/{note}/comments
     */
    public function indexForNote(string $note)
    {
        $noteModel = Note::find($note);

        if (!$noteModel) {
            return response()->json([
                'message' => 'Poznamka nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('viewForNote', [Comment::class, $noteModel]);

        $comments = Comment::query()
            ->select(['id', 'user_id', 'note_id', 'task_id', 'title', 'body', 'created_at'])
            ->where('note_id', $noteModel->id)
            ->whereNull('task_id')
            ->with(['user:id,first_name,last_name'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'note_id' => $noteModel->id,
            'comments' => $comments,
        ], Response::HTTP_OK);
    }

    /**
     * Get comments for a task.
     * GET /api/notes/{note}/tasks/{task}/comments
     */
    public function indexForTask(string $note, string $task)
    {
        $noteModel = Note::find($note);

        if (!$noteModel) {
            return response()->json([
                'message' => 'Poznamka nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $taskModel = Task::where('id', $task)
            ->where('note_id', $noteModel->id)
            ->first();

        if (!$taskModel) {
            return response()->json([
                'message' => 'Uloha nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('viewForTask', [Comment::class, $taskModel]);

        $comments = Comment::query()
            ->select(['id', 'user_id', 'note_id', 'task_id', 'title', 'body', 'created_at'])
            ->where('task_id', $taskModel->id)
            ->with(['user:id,first_name,last_name'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'note_id' => $noteModel->id,
            'task_id' => $taskModel->id,
            'comments' => $comments,
        ], Response::HTTP_OK);
    }

    /**
     * Create a comment for a note.
     * POST /api/notes/{note}/comments
     */
    public function storeForNote(Request $request, string $note)
    {
        $noteModel = Note::find($note);

        if (!$noteModel) {
            return response()->json([
                'message' => 'Poznamka nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('createForNote', [Comment::class, $noteModel]);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'note_id' => $noteModel->id,
            'task_id' => null,
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
        ]);

        return response()->json([
            'message' => 'Komentar bol uspesne vytvoreny.',
            'comment' => $comment->load(['user:id,first_name,last_name']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Create a comment for a task.
     * POST /api/notes/{note}/tasks/{task}/comments
     */
    public function storeForTask(Request $request, string $note, string $task)
    {
        $noteModel = Note::find($note);

        if (!$noteModel) {
            return response()->json([
                'message' => 'Poznamka nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $taskModel = Task::where('id', $task)
            ->where('note_id', $noteModel->id)
            ->first();

        if (!$taskModel) {
            return response()->json([
                'message' => 'Uloha nenajdena.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('createForTask', [Comment::class, $taskModel]);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'note_id' => $noteModel->id,
            'task_id' => $taskModel->id,
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
        ]);

        return response()->json([
            'message' => 'Komentar bol uspesne vytvoreny.',
            'comment' => $comment->load(['user:id,first_name,last_name']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a comment.
     * PATCH /api/comments/{comment}
     */
    public function update(Request $request, string $comment)
    {
        $commentModel = Comment::find($comment);

        if (!$commentModel) {
            return response()->json([
                'message' => 'Komentar nenajdeny.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $commentModel);

        $validated = $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
        ]);

        $commentModel->update($validated);

        return response()->json([
            'message' => 'Komentar bol uspesne upraveny.',
            'comment' => $commentModel->load(['user:id,first_name,last_name']),
        ], Response::HTTP_OK);
    }

    /**
     * Delete a comment.
     * DELETE /api/comments/{comment}
     */
    public function destroy(string $comment)
    {
        $commentModel = Comment::find($comment);

        if (!$commentModel) {
            return response()->json([
                'message' => 'Komentar nenajdeny.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $commentModel);

        $commentModel->delete();

        return response()->json([
            'message' => 'Komentar bol uspesne odstraneny.'
        ], Response::HTTP_OK);
    }
}
