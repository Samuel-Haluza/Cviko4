<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
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

// ===== CATEGORIES ENDPOINTS =====
Route::apiResource('categories', CategoryController::class);

// Custom category endpoints
Route::get('categories-with-count', [CategoryController::class, 'getCategoriesWithCount']);
Route::get('categories-search', [CategoryController::class, 'search']);