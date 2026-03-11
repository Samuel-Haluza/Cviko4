<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = DB::table('notes')
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::table('notes')->insert([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.'
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = DB::table('notes')->where('id', $id)->first();

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        DB::table('notes')->where('id', $id)->update([
            'title' => $request->title,
            'body' => $request->body,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne aktualizovaná.'
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id) // toto je soft delete
    {
        $note = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        DB::table('notes')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

//        DB::table('notes')->where('id', $id)->delete();

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

    /**
     * Get statistics of notes by status.
     * GET /api/notes/stats/status
     */
    public function statsByStatus()
    {
        $stats = DB::table('notes')
            ->whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'stats' => $stats
        ], Response::HTTP_OK);
    }

    /**
     * Archive old draft notes.
     * PATCH /api/notes/actions/archive-old-drafts
     */
    public function archiveOldDrafts()
    {
        $thirtyDaysAgo = now()->subDays(30);

        $updated = DB::table('notes')
            ->where('status', 'draft')
            ->where('updated_at', '<', $thirtyDaysAgo)
            ->whereNull('deleted_at')
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Staré poznámky boli archivované.',
            'archived_count' => $updated
        ], Response::HTTP_OK);
    }

    /**
     * Get notes with categories for a specific user.
     * GET /api/users/{user}/notes
     */
    public function userNotesWithCategories(string $user)
    {
        $notes = DB::table('notes')
            ->where('user_id', $user)
            ->whereNull('deleted_at')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($notes->isEmpty()) {
            return response()->json([
                'message' => 'Používateľ nemá žiadne poznámky.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Enrich notes with categories
        $notesWithCategories = $notes->map(function ($note) {
            $categories = DB::table('categories')
                ->join('note_category', 'categories.id', '=', 'note_category.category_id')
                ->where('note_category.note_id', $note->id)
                ->select('categories.id', 'categories.name')
                ->get();

            return (array)$note + ['categories' => $categories];
        });

        return response()->json([
            'notes' => $notesWithCategories
        ], Response::HTTP_OK);
    }

    /**
     * Search notes by title or body.
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

        $notes = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('body', 'LIKE', '%' . $query . '%')
            ->orderBy('updated_at', 'desc')
            ->get();

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
        $notes = DB::table('notes')
            ->where('is_pinned', true)
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'notes' => $notes
        ], Response::HTTP_OK);
    }

    /**
     * Get notes by status.
     * GET /api/notes-actions/by-status?status=published
     */
    public function getNotesByStatus(Request $request)
    {
        $status = $request->query('status');

        if (!$status) {
            return response()->json([
                'message' => 'Parameter "status" je povinný.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validStatuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'message' => 'Neplatný status. Povolené: ' . implode(', ', $validStatuses)
            ], Response::HTTP_BAD_REQUEST);
        }

        $notes = DB::table('notes')
            ->where('status', $status)
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'status' => $status,
            'notes' => $notes,
            'total' => count($notes)
        ], Response::HTTP_OK);
    }

}