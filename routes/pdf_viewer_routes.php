<?php
// routes/pdf_viewer_routes.php (include these in your routes/web.php or require this file)
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// Demo page (no auth for quick test; secure as needed)
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

