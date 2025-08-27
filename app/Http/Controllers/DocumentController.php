<?php
// app/Http/Controllers/DocumentController.php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    // ---------- Demo uploader/list page ----------
    public function uploaderForm(Request $request)
    {
        $docs = Document::latest()->take(10)->get();
        return view('docs.uploader', compact('docs'));
    }

    // ---------- Viewer page (Blade + pdf.js core) ----------
    public function viewer($id, Request $request)
    {
        $slug = (string) $request->query('s');
        $link = $this->validateLink($id, $slug);
        $doc  = $link->document;

        $allowDownload = is_null($link->allow_download)
            ? (bool) $doc->allow_download
            : (bool) $link->allow_download;

        // per-view one-time nonce (stored in session)
        $nonce = Str::random(32);
        $request->session()->put("pdf_nonce:{$doc->id}:{$link->slug}", $nonce);

        return view('docs.viewer', [
            'doc'           => $doc,
            'slug'          => $link->slug,
            'allowDownload' => $allowDownload,
            // Named routes for JS / forms
            'streamRoute'   => route('documents.stream.data', ['id' => $doc->id]),
            'downloadRoute' => route('documents.download',     ['id' => $doc->id]),
            'nonce'         => $nonce,
        ]);
    }

    // ---------- POST-only: return PDF bytes for pdf.js via ArrayBuffer ----------
    public function streamData($id, Request $request)
    {
        // Hard 403 instead of validation exceptions
        $slug  = (string) $request->input('s', '');
        $nonce = (string) $request->input('nonce', '');

        if (strlen($slug) < 16 || strlen($nonce) < 32) {
            abort(403, 'FORBIDDEN ACCESS');
        }

        $link = $this->validateLink($id, $slug);
        $doc  = $link->document;

        // check & consume nonce (one-time)
        $key = "pdf_nonce:{$doc->id}:{$link->slug}";
        $sessionNonce = $request->session()->pull($key);
        if (!$sessionNonce || !hash_equals($sessionNonce, $nonce)) {
            abort(403, 'FORBIDDEN ACCESS');
        }

        $abs = public_path($doc->storage_path);
        abort_unless(is_file($abs), 404, 'File missing');

        return response()->file($abs, [
            'Content-Type'           => $doc->mime ?: 'application/pdf',
            'Content-Disposition'    => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'no-store, must-revalidate',
            'Pragma'                 => 'no-cache',
        ]);
    }

    // ---------- POST-only download (only when allowed) ----------
    public function download($id, Request $request)
    {
        $slug = (string) $request->input('s', '');
        if (strlen($slug) < 16) {
            abort(403, 'FORBIDDEN ACCESS');
        }

        $link = $this->validateLink($id, $slug);
        $doc  = $link->document;

        $allowed = is_null($link->allow_download)
            ? (bool) $doc->allow_download
            : (bool) $link->allow_download;

        abort_unless($allowed, 403, 'Downloading disabled for this file.');

        $abs = public_path($doc->storage_path);
        abort_unless(is_file($abs), 404, 'File missing');

        return response()->download($abs, $doc->original_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ---------- Create a tokened share link (AJAX from uploader page) ----------
    public function createLink($id, Request $request)
    {
        $doc = Document::findOrFail($id);
        $ttlMinutes = (int) $request->input('ttl_min', 0); // 0 => never
        $expiresAt  = $ttlMinutes > 0 ? now()->addMinutes($ttlMinutes) : null;

        $link = DocLink::create([
            'document_id'    => $doc->id,
            'slug'           => Str::random(40),
            'expires_at'     => $expiresAt,
            'allow_download' => $request->boolean('allow_download', false),
            'max_views'      => $request->input('max_views'),
        ]);

        return response()->json([
            'view_url'         => route('documents.viewer', ['id' => $doc->id, 's' => $link->slug]),
            'download_allowed' => (bool) $link->allow_download,
            'expires_at'       => $link->expires_at?->toIso8601String(),
        ]);
    }

    // ---------- Upload to /public/uploads/docs ----------
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimetypes:application/pdf|file|max:51200', // 50MB
        ]);

        $file = $request->file('file');

        // Ensure folder exists: public/uploads/docs
        $destDir = public_path('uploads/docs');
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        // Random safe filename in public/
        $ext  = $file->getClientOriginalExtension() ?: 'pdf';
        $rand = bin2hex(random_bytes(16)) . '.' . $ext; // 32-char hex + extension
        $file->move($destDir, $rand);

        // Save relative path from /public
        $publicRelPath = 'uploads/docs/' . $rand;

        $doc = Document::create([
            'user_id'        => optional($request->user())->id,
            'original_name'  => $file->getClientOriginalName(),
            'storage_path'   => $publicRelPath, // relative to /public
            'mime'           => 'application/pdf',
            'size'           => filesize(public_path($publicRelPath)),
            'allow_download' => (bool) $request->boolean('allow_download', false),
        ]);

        return redirect()->route('documents.uploader')->with('uploaded_id', $doc->id);
    }

    // ---------- Shared guard ----------
    private function validateLink($docId, string $slug): DocLink
    {
        abort_if($slug === '' || strlen($slug) < 16, 403, 'Bad link');

        $link = DocLink::with('document')
            ->where('document_id', $docId)
            ->where('slug', $slug)
            ->first();

        abort_unless($link, 403, 'Invalid link');
        abort_if($link->isExpired(), 403, 'Link expired');
        abort_if($link->isViewLimitHit(), 403, 'View limit reached');

        $link->increment('views');
        return $link;
    }
}
