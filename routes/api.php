<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// ===== NOTES ENDPOINTS =====
Route::apiResource('notes', NoteController::class);

// Custom note endpoints
Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);
Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);
Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);
Route::get('notes-actions/search', [NoteController::class, 'search']);
Route::get('notes-actions/pinned', [NoteController::class, 'getPinnedNotes']);
Route::get('notes-actions/by-status', [NoteController::class, 'getNotesByStatus']);

// ===== CATEGORIES ENDPOINTS =====
Route::apiResource('categories', CategoryController::class);

// Custom category endpoints
Route::get('categories-with-count', [CategoryController::class, 'getCategoriesWithCount']);
Route::get('categories-search', [CategoryController::class, 'search']);