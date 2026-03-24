<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * Get all tasks for a specific note
     * GET /api/notes/{noteId}/tasks
     */
    public function index($noteId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Používame relationship: $note->tasks()
        $tasks = $note->tasks()
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'note_id' => $noteId,
            'tasks' => $tasks,
            'total' => $tasks->count(),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new task for a specific note
     * POST /api/notes/{noteId}/tasks
     * 
     * Request body:
     * {
     *   "title": "string (required, 3-255 chars)",
     *   "description": "string (optional)",
     *   "is_completed": "boolean (optional, default: false)"
     * }
     */
    public function store(Request $request, $noteId)
    {
        // Skontroluj či poznámka existuje
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validácia vstupných dát
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_completed' => ['sometimes', 'boolean'],
        ], [
            'title.required' => 'Pole "title" je povinné.',
            'title.min' => 'Názov úlohy musí mať minimálne 3 znaky.',
            'title.max' => 'Názov úlohy nesmie byť dlhší ako 255 znakov.',
            'description.max' => 'Popis úlohy nesmie byť dlhší ako 1000 znakov.',
            'is_completed.boolean' => 'Pole "is_completed" musí byť boolean (true/false).',
        ]);

        // Vytvorenie novej úlohy cez relationship - ELOQUENT APPROACH
        $task = $note->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => $validated['is_completed'] ?? false,
        ]);

        return response()->json([
            'message' => 'Úloha bola úspešne vytvorená.',
            'task' => $task,
        ], Response::HTTP_CREATED);
    }

    /**
     * Get a specific task
     * GET /api/notes/{noteId}/tasks/{taskId}
     */
    public function show($noteId, $taskId)
    {
        // Skontroluj či poznámka existuje
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'task' => $task,
        ], Response::HTTP_OK);
    }

    /**
     * Update a specific task
     * PUT/PATCH /api/notes/{noteId}/tasks/{taskId}
     * 
     * Request body:
     * {
     *   "title": "string (optional)",
     *   "description": "string (optional)",
     *   "is_completed": "boolean (optional)"
     * }
     */
    public function update(Request $request, $noteId, $taskId)
    {
        // Skontroluj či poznámka existuje
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validácia vstupných dát
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_completed' => ['sometimes', 'boolean'],
        ], [
            'title.min' => 'Názov úlohy musí mať minimálne 3 znaky.',
            'title.max' => 'Názov úlohy nesmie byť dlhší ako 255 znakov.',
            'description.max' => 'Popis úlohy nesmie byť dlhší ako 1000 znakov.',
            'is_completed.boolean' => 'Pole "is_completed" musí byť boolean (true/false).',
        ]);

        // Aktualizuj úlohu
        $task->update($validated);

        return response()->json([
            'message' => 'Úloha bola úspešne aktualizovaná.',
            'task' => $task->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Delete a specific task (Soft Delete)
     * DELETE /api/notes/{noteId}/tasks/{taskId}
     */
    public function destroy($noteId, $taskId)
    {
        // Skontroluj či poznámka existuje
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Soft delete
        $task->delete();

        return response()->json([
            'message' => 'Úloha bola úspešne odstránená.'
        ], Response::HTTP_OK);
    }

    /**
     * Toggle task completion status
     * PATCH /api/notes/{noteId}/tasks/{taskId}/toggle
     */
    public function toggle($noteId, $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return response()->json([
            'message' => 'Stav úlohy bol zmenený.',
            'task' => $task->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Mark task as completed
     * PATCH /api/notes/{noteId}/tasks/{taskId}/complete
     */
    public function complete($noteId, $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task->update([
            'is_completed' => true,
        ]);

        return response()->json([
            'message' => 'Úloha bola označená ako hotová.',
            'task' => $task->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Mark task as incomplete
     * PATCH /api/notes/{noteId}/tasks/{taskId}/incomplete
     */
    public function incomplete($noteId, $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ziskaj task cez relationship
        $task = $note->tasks()->find($taskId);

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task->update([
            'is_completed' => false,
        ]);

        return response()->json([
            'message' => 'Úloha bola označená ako nedokončená.',
            'task' => $task->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Get statistics of tasks for a note
     * GET /api/notes/{noteId}/tasks/stats/overview
     */
    public function stats($noteId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Počítaj úlohy cez relationship
        $total = $note->tasks()->count();
        $completed = $note->tasks()->where('is_completed', true)->count();
        $pending = $total - $completed;
        $completionPercentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return response()->json([
            'note_id' => $noteId,
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'pending_tasks' => $pending,
            'completion_percentage' => $completionPercentage . '%',
        ], Response::HTTP_OK);
    }
}
