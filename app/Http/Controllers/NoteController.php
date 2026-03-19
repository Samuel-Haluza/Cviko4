<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::query()
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $note = Note::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.',
            'note' => $note,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['note' => $note], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->update([
            'title' => $request->title ?? $note->title,
            'body' => $request->body ?? $note->body,
            'status' => $request->status ?? $note->status,
            'is_pinned' => $request->is_pinned ?? $note->is_pinned,
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne aktualizovaná.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->delete(); // soft delete

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

    /**
     * ===== PIN/UNPIN ENDPOINTS =====
     */

    /**
     * Pin a note.
     * PATCH /api/notes/{id}/pin
     */
    public function pin(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->pin();

        return response()->json([
            'message' => 'Poznámka bola úspešne pripnutá.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Unpin a note.
     * PATCH /api/notes/{id}/unpin
     */
    public function unpin(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->unpin();

        return response()->json([
            'message' => 'Poznámka bola úspešne odopnutá.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Toggle pin status.
     * PATCH /api/notes/{id}/toggle-pin
     */
    public function togglePin(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->togglePin();

        return response()->json([
            'message' => 'Stav pripnutia poznámky bol zmenený. is_pinned: ' . ($note->is_pinned ? 'true' : 'false'),
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * ===== STATUS CHANGE ENDPOINTS =====
     */

    /**
     * Publish a note.
     * PATCH /api/notes/{id}/publish
     */
    public function publish(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->publish();

        return response()->json([
            'message' => 'Poznámka bola úspešne publikovaná.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Archive a note.
     * PATCH /api/notes/{id}/archive
     */
    public function archive(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->archive();

        return response()->json([
            'message' => 'Poznámka bola úspešne archivovaná.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Set note to draft.
     * PATCH /api/notes/{id}/draft
     */
    public function draft(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->draft();

        return response()->json([
            'message' => 'Poznámka bola úspešne prevedená na koncept.',
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * ===== EXISTING CUSTOM ENDPOINTS =====
     */

    /**
     * Get statistics by status.
     * GET /api/notes/stats/status
     */
    public function statsByStatus()
    {
        $stats = Note::getStatsByStatus();

        return response()->json(['stats' => $stats], Response::HTTP_OK);
    }

    /**
     * Archive old drafts (older than 30 days).
     * PATCH /api/notes/actions/archive-old-drafts
     */
    public function archiveOldDrafts()
    {
        $affected = Note::archiveOldDrafts();

        return response()->json([
            'message' => 'Staré koncepty boli archivované.',
            'affected_rows' => $affected,
        ]);
    }

    /**
     * Get user notes with categories.
     * GET /api/users/{userId}/notes
     */
    public function userNotesWithCategories(string $userId)
    {
        $notes = Note::getUserNotesWithCategories($userId);

        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    /**
     * Search notes.
     * GET /api/notes-actions/search?q=search_term
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        if (empty($query)) {
            return response()->json([
                'message' => 'Vyhľadávací parameter "q" je povinný.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $notes = Note::search($query);

        return response()->json([
            'search_query' => $query,
            'notes' => $notes,
            'total' => count($notes)
        ], Response::HTTP_OK);
    }

    /**
     * Get pinned notes.
     * GET /api/notes-actions/pinned
     */
    public function getPinnedNotes()
    {
        $notes = Note::getPinned();

        return response()->json([
            'notes' => $notes,
            'total' => count($notes)
        ], Response::HTTP_OK);
    }

    /**
     * Get notes by status.
     * GET /api/notes-actions/by-status?status=published
     */
    public function getNotesByStatus(Request $request)
    {
        $status = $request->query('status', 'published');

        if (!in_array($status, ['draft', 'archived', 'published'])) {
            return response()->json([
                'message' => 'Neplatný status. Povolené hodnoty: draft, archived, published'
            ], Response::HTTP_BAD_REQUEST);
        }

        $notes = Note::getByStatus($status);

        return response()->json([
            'status' => $status,
            'notes' => $notes,
            'total' => count($notes)
        ], Response::HTTP_OK);
    }
}