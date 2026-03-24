<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// ===== NOTES ENDPOINTS =====
Route::apiResource('notes', NoteController::class);

// Note Pin/Unpin endpoints
Route::patch('notes/{id}/pin', [NoteController::class, 'pin']);
Route::patch('notes/{id}/unpin', [NoteController::class, 'unpin']);
Route::patch('notes/{id}/toggle-pin', [NoteController::class, 'togglePin']);

// Note Status endpoints
Route::patch('notes/{id}/publish', [NoteController::class, 'publish']);
Route::patch('notes/{id}/archive', [NoteController::class, 'archive']);
Route::patch('notes/{id}/draft', [NoteController::class, 'draft']);

// Custom note endpoints
Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);
Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);
Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);
Route::get('notes-actions/search', [NoteController::class, 'search']);
Route::get('notes-actions/pinned', [NoteController::class, 'getPinnedNotes']);
Route::get('notes-actions/by-status', [NoteController::class, 'getNotesByStatus']);

// ===== TASKS ENDPOINTS (Nested under notes) =====
Route::get('notes/{noteId}/tasks', [TaskController::class, 'index']);
Route::post('notes/{noteId}/tasks', [TaskController::class, 'store']);
Route::get('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'show']);
Route::put('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'update']);
Route::patch('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'update']);
Route::delete('notes/{noteId}/tasks/{taskId}', [TaskController::class, 'destroy']);

// Task custom endpoints
Route::patch('notes/{noteId}/tasks/{taskId}/toggle', [TaskController::class, 'toggle']);
Route::patch('notes/{noteId}/tasks/{taskId}/complete', [TaskController::class, 'complete']);
Route::patch('notes/{noteId}/tasks/{taskId}/incomplete', [TaskController::class, 'incomplete']);
Route::get('notes/{noteId}/tasks/stats/overview', [TaskController::class, 'stats']);

// ===== CATEGORIES ENDPOINTS =====
Route::apiResource('categories', CategoryController::class);

// Custom category endpoints
Route::get('categories-with-count', [CategoryController::class, 'getCategoriesWithCount']);
Route::get('categories-search', [CategoryController::class, 'search']);