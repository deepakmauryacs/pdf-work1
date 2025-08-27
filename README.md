# Laravel 12 — Pure Custom PDF Viewer (Download Permission On/Off)

This package contains only the module files. Copy them into your existing Laravel project.

## 1) Copy files
- Merge `app/`, `database/`, and `resources/` folders into your app root.
- Put `routes/pdf_viewer_routes.php` anywhere, then **require it** from `routes/web.php`:
  ```php
  require __DIR__.'/pdf_viewer_routes.php';
  ```
- Open `config/filesystems.php` and add the **private** disk inside the `disks` array (copy from `config/filesystems-extra.php`).

## 2) Migrate
```bash
php artisan migrate
```

## 3) Test via built-in demo page
- Start your server: `php artisan serve`
- Visit: `http://127.0.0.1:8000/docs`
  - Upload a PDF
  - Create a link (set TTL=0 for never expire, toggle "Allow Download"; expiry date uses dd-mm-yyyy format)
  - Open the `view_url` shown in results
  - If download is disabled, the ⬇ button is disabled and direct `download` route returns 403

## Notes
- pdf.js is loaded via CDN (no `viewer.html`).
- Files are **not public**: stored under `storage/app/private/...` and streamed by controller.
- To customize auth/ownership, add authorization checks in `createLink()` and `upload()`.

## Endpoints
- `GET /documents/{id}/view?s=TOKEN` → Viewer UI
- `POST /documents/{id}/stream-data` (body: `s=TOKEN`, `nonce`) → PDF bytes for pdf.js
- `POST /documents/{id}/download` (body: `s=TOKEN`) → Attachment (403 when not allowed)
- `POST /documents/{id}/link` → JSON { view_url, stream_url, ... }
- `POST /documents/upload` → Upload PDF (form on /docs adds CSRF automatically)

## Security Reminders
- Disabling download prevents our **download route**, but users can still screen‑capture or inspect traffic. For sensitive docs, add watermark overlays in the canvas or serve low‑res previews.
