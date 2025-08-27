<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes (Laravel 11)
|--------------------------------------------------------------------------
| - Viewer uses token (?s=slug) and session-bound nonce
| - PDF bytes are served ONLY via POST (no GET stream URL)
| - Download is also POST-only and allowed only when permitted
| - Any non-POST hit on stream/download returns 403 FORBIDDEN ACCESS
*/

/**
 * Demo page to upload and mint links
 * GET /docs
 */
Route::get('/docs', [DocumentController::class, 'uploaderForm'])->name('documents.uploader');

// Upload (uses CSRF from form on /docs)
Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');

// Viewer + stream + download
Route::get('/documents/{id}/view', [DocumentController::class, 'viewer'])
    ->name('documents.viewer');

Route::post('/documents/{id}/stream-data', [DocumentController::class, 'streamData'])
    ->name('documents.stream.data');
Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], '/documents/{id}/stream-data', function () {
    abort(403, 'FORBIDDEN ACCESS');
});

Route::post('/documents/{id}/download', [DocumentController::class, 'download'])
    ->name('documents.download');
Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], '/documents/{id}/download', function () {
    abort(403, 'FORBIDDEN ACCESS');
});

// Create share link
Route::post('/documents/{id}/link', [DocumentController::class, 'createLink'])->name('documents.link.create');
