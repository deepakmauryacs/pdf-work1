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
Route::get('/docs', [DocumentController::class, 'uploaderForm'])
    ->name('documents.uploader');

/**
 * Upload a PDF (saves to /public/uploads/docs)
 * POST /documents/upload
 */
Route::post('/documents/upload', [DocumentController::class, 'upload'])
    ->name('documents.upload');

/**
 * Viewer page (no bytes here; only shows UI and prepares POST+nonce)
 * GET /documents/{id}/view?s=SLUG
 */
Route::get('/documents/{id}/view', [DocumentController::class, 'viewer'])
    ->name('documents.viewer');

/**
 * POST-only: return PDF bytes (inline) for pdf.js via ArrayBuffer
 * POST /documents/{id}/stream-data
 */
Route::post('/documents/{id}/stream-data', [DocumentController::class, 'streamData'])
    ->name('documents.stream.data')
    // ->middleware('throttle:20,1') // optional rate-limit
;

/**
 * BLOCK any other method on stream-data with 403
 */
Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], '/documents/{id}/stream-data', function () {
    abort(403, 'FORBIDDEN ACCESS');
});

/**
 * POST-only download (only when allow_download=true)
 * POST /documents/{id}/download
 */
Route::post('/documents/{id}/download', [DocumentController::class, 'download'])
    ->name('documents.download')
    // ->middleware('throttle:10,1') // optional rate-limit
;

/**
 * BLOCK any other method on download with 403
 */
Route::match(['GET', 'PUT', 'PATCH', 'DELETE'], '/documents/{id}/download', function () {
    abort(403, 'FORBIDDEN ACCESS');
});

/**
 * Create a tokened share link (AJAX from uploader page)
 * POST /documents/{id}/link
 */
Route::post('/documents/{id}/link', [DocumentController::class, 'createLink'])
    ->name('documents.link.create');