<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Throwable;

class AttachmentController extends Controller
{
    public function index(Note $note)
    {
        $this->authorize('viewForNote', [Attachment::class, $note]);

        $attachments = $note->attachments()
            ->select(['id', 'public_id', 'collection', 'visibility', 'disk', 'path', 'original_name', 'stored_name', 'mime_type', 'size', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'note_id' => $note->id,
            'attachments' => $attachments,
        ], Response::HTTP_OK);
    }

    public function store(Request $request, Note $note)
    {
        $this->authorize('createForNote', [Attachment::class, $note]);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max('5mb')],
        ]);

        $disk = 'local';
        $created = [];
        $storedPaths = [];

        try {
            DB::beginTransaction();

            foreach ($validated['files'] as $file) {
                $directory = 'attachments/notes/' . $note->id . '/' . now()->format('Y/m');
                $path = $file->store($directory, $disk);
                $storedPaths[] = $path;

                $created[] = $note->attachments()->create([
                    'public_id' => (string) Str::ulid(),
                    'collection' => 'attachment',
                    'visibility' => 'private',
                    'disk' => $disk,
                    'path' => $path,
                    'stored_name' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => (string) $file->getMimeType(),
                    'size' => (int) $file->getSize(),
                ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            foreach ($storedPaths as $path) {
                Storage::disk($disk)->delete($path);
            }

            return response()->json([
                'message' => 'Prilohy sa nepodarilo ulozit.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Prilohy boli nahrane.',
            'attachments' => $created,
        ], Response::HTTP_CREATED);
    }

    public function link(Request $request, Attachment $attachment)
    {
        $this->authorize('link', $attachment);

        if ($attachment->visibility === 'public') {
            return response()->json([
                'url' => $attachment->publicUrl(),
                'expires_at' => null,
            ], Response::HTTP_OK);
        }

        $expiresAt = now()->addSeconds(30);

        $url = URL::temporarySignedRoute(
            'attachments.download',
            $expiresAt,
            ['attachment' => $attachment->id]
        );

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
        ], Response::HTTP_OK);
    }

    public function download(Request $request, string $attachment)
    {
        $attachmentModel = Attachment::find($attachment);

        if (!$attachmentModel) {
            return response()->json([
                'message' => 'Priloha nenajdena.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($request->user()) {
            $this->authorize('view', $attachmentModel);
        }

        if (!Storage::disk($attachmentModel->disk)->exists($attachmentModel->path)) {
            return response()->json([
                'message' => 'Subor neexistuje na disku.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk($attachmentModel->disk)->download(
            $attachmentModel->path,
            $attachmentModel->original_name,
            ['Content-Type' => $attachmentModel->mime_type]
        );
    }

    public function destroy(Request $request, string $attachment)
    {
        $attachmentModel = Attachment::find($attachment);

        if (!$attachmentModel) {
            return response()->json([
                'message' => 'Priloha nenajdena.',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $attachmentModel);

        DB::transaction(function () use ($attachmentModel) {
            Storage::disk($attachmentModel->disk)->delete($attachmentModel->path);
            $attachmentModel->delete();
        });

        return response()->json([
            'message' => 'Priloha bola odstranena.',
        ], Response::HTTP_OK);
    }
}
