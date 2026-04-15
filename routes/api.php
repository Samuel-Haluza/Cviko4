<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/logout-all-devices', [AuthController::class, 'logoutAllDevices']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::patch('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/profile-photo', [AuthController::class, 'storeProfilePhoto']);
    Route::delete('auth/profile-photo', [AuthController::class, 'destroyProfilePhoto']);
});

Route::apiResource('notes', NoteController::class);
Route::get('notes/my/own', [NoteController::class, 'myNotes']);

Route::patch('notes/{id}/pin', [NoteController::class, 'pin']);
Route::patch('notes/{id}/unpin', [NoteController::class, 'unpin']);
Route::patch('notes/{id}/toggle-pin', [NoteController::class, 'togglePin']);

Route::patch('notes/{id}/publish', [NoteController::class, 'publish']);
Route::patch('notes/{id}/archive', [NoteController::class, 'archive']);
Route::patch('notes/{id}/draft', [NoteController::class, 'draft']);

Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);
Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);
Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);
Route::get('notes-actions/search', [NoteController::class, 'search']);
Route::get('notes-actions/pinned', [NoteController::class, 'getPinnedNotes']);
Route::get('notes-actions/by-status', [NoteController::class, 'getNotesByStatus']);

Route::get('notes/{noteId}/tasks', [TaskController::class, 'index']);
Route::post('notes/{noteId}/tasks', [TaskController::class, 'store']);
Route::get('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'show']);
Route::put('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'update']);
Route::patch('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'update']);
Route::delete('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'destroy']);


Route::patch('notes/{noteId}/tasks/{taskId}/toggle', [TaskController::class, 'toggle']);
Route::patch('notes/{noteId}/tasks/{taskId}/complete', [TaskController::class, 'complete']);
Route::patch('notes/{noteId}/tasks/{taskId}/incomplete', [TaskController::class, 'incomplete']);
Route::get('notes/{noteId}/tasks/stats/overview', [TaskController::class, 'stats']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notes/{note}/attachments', [AttachmentController::class, 'index']);
    Route::post('notes/{note}/attachments', [AttachmentController::class, 'store'])->middleware('premium');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);
    Route::get('attachments/{attachment}/link', [AttachmentController::class, 'link']);
});

Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
    ->middleware('signed')
    ->name('attachments.download');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('notes/{note}/comments', [CommentController::class, 'indexForNote']);
    Route::post('notes/{note}/comments', [CommentController::class, 'storeForNote']);
    Route::get('notes/{note}/tasks/{task}/comments', [CommentController::class, 'indexForTask']);
    Route::post('notes/{note}/tasks/{task}/comments', [CommentController::class, 'storeForTask']);
    Route::patch('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('categories-with-count', [CategoryController::class, 'getCategoriesWithCount']);
    Route::get('categories-search', [CategoryController::class, 'search']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::patch('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
});