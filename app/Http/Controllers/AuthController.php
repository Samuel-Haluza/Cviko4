<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:128'],
            'last_name' => ['required', 'string', 'min:2', 'max:128'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrácia prebehla úspešne.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Nesprávny email alebo heslo.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Prihlásenie bolo úspešné.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Používateľ bol odhlásený z aktuálneho zariadenia.',
        ], Response::HTTP_OK);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('profilePhoto');

        return response()->json([
            'user' => $user,
            'profile_photo_url' => $user->profilePhoto?->publicUrl(),
            'active_sessions' => $user->tokens()->count(),
        ], Response::HTTP_OK);
    }

    public function logoutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Odhlasenie zo všetkych zariadeni bolo uspesne.',
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'string', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Aktualne heslo je nespravne.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Heslo bolo uspesne zmenene.',
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'min:2', 'max:128'],
            'last_name' => ['nullable', 'string', 'min:2', 'max:128'],
        ]);

        $user = $request->user();

        $user->update(array_filter($validated, function ($value) {
            return !is_null($value);
        }));

        return response()->json([
            'message' => 'Profil bol uspesne aktualizovany.',
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function storeProfilePhoto(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', File::image()->max('3mb')],
        ]);

        $user = $request->user();
        $file = $validated['file'];

        $disk = 'public';
        $directory = 'profile_photos/users/' . $user->id;
        $path = null;

        try {
            DB::beginTransaction();

            $oldProfilePhoto = $user->profilePhoto;

            $path = $file->store($directory, $disk);

            $newPhoto = $user->profilePhoto()->create([
                'public_id' => (string) Str::ulid(),
                'collection' => 'profile_photo',
                'visibility' => 'public',
                'disk' => $disk,
                'path' => $path,
                'stored_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) $file->getMimeType(),
                'size' => (int) $file->getSize(),
            ]);

            if ($oldProfilePhoto) {
                Storage::disk($oldProfilePhoto->disk)->delete($oldProfilePhoto->path);
                $oldProfilePhoto->delete();
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            if ($path) {
                Storage::disk($disk)->delete($path);
            }

            return response()->json([
                'message' => 'Profilovu fotku sa nepodarilo ulozit.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Profilova fotka bola ulozena.',
            'profile_photo' => $newPhoto,
            'profile_photo_url' => $newPhoto->publicUrl(),
        ], Response::HTTP_CREATED);
    }

    public function destroyProfilePhoto(Request $request)
    {
        $attachment = $request->user()->profilePhoto;

        if (!$attachment) {
            return response()->json([
                'message' => 'Profilova fotka neexistuje.',
            ], Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->delete();
        });

        return response()->json([
            'message' => 'Profilova fotka bola odstranena.',
        ], Response::HTTP_OK);
    }
}
