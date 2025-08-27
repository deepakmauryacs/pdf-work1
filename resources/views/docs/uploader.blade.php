@php($uploadedId = session('uploaded_id'))
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Docs — Upload & Link</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
  <style> body{padding:24px} .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;} </style>
</head>
<body class="container">
  <h1 class="mb-4">PDF Upload & Link (Demo)</h1>

  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card p-3">
        <form id="uploadForm" action="{{ route('documents.upload') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="mb-2">
            <label class="form-label">Choose PDF</label>
            <input type="file" class="form-control" name="file" accept="application/pdf" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="allow_download" name="allow_download">
            <label class="form-check-label" for="allow_download">Allow Download (default for this document)</label>
          </div>
          <button class="btn btn-primary" type="submit">Upload</button>
        </form>
        <pre id="uploadResult" class="mt-3 mono small"></pre>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card p-3">
        <h5>Create Share Link</h5>
        <div class="mb-2">
          <label class="form-label">Document</label>
          <select id="docId" class="form-select">
            @foreach($docs as $d)
              <option value="{{ $d->id }}" @selected($uploadedId==$d->id)>{{ $d->id }} — {{ $d->original_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="row g-2">
          <div class="col">
            <label class="form-label">TTL (minutes, 0 = never)</label>
            <input id="ttl" type="number" class="form-control" value="60">
          </div>
          <div class="col">
            <label class="form-label">Max Views (empty = unlimited)</label>
            <input id="views" type="number" class="form-control">
          </div>
        </div>
        <div class="mb-2 mt-2">
          <label class="form-label">Expiry Date (dd-mm-yyyy)</label>
          <input id="expiry" type="text" class="form-control" placeholder="dd-mm-yyyy">
        </div>
        <div class="form-check my-2">
          <input class="form-check-input" type="checkbox" id="link_dl">
          <label class="form-check-label" for="link_dl">Allow Download for this Link</label>
        </div>
        <button id="createBtn" class="btn btn-success">Create Link</button>
        <pre id="result" class="mt-3 mono small"></pre>
      </div>
    </div>
  </div>
  <script>
    const csrfToken = document.querySelector('meta[name=csrf-token]').content;

    // Upload form AJAX with client-side validation
    document.getElementById('uploadForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fileInput = e.target.querySelector('input[name=file]');
      if (!fileInput.files.length) {
        alert('Please choose a PDF file.');
        return;
      }
      if (fileInput.files[0].type !== 'application/pdf') {
        alert('File must be a PDF.');
        return;
      }
      const formData = new FormData(e.target);
      const res = await fetch(e.target.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
      });
      const data = await res.json();
      document.getElementById('uploadResult').textContent = JSON.stringify(data, null, 2);
    });

    // Create link AJAX with client-side validation
    document.getElementById('createBtn').addEventListener('click', async () => {
      const id = document.getElementById('docId').value;
      const body = new FormData();
      body.append('ttl_min', document.getElementById('ttl').value || 0);
      const v = document.getElementById('views').value;
      if (v !== '') body.append('max_views', v);
      if (document.getElementById('link_dl').checked) body.append('allow_download', '1');
      const expiry = document.getElementById('expiry').value.trim();
      if (expiry !== '') {
        if (!/^\d{2}-\d{2}-\d{4}$/.test(expiry)) {
          alert('Invalid date format. Use dd-mm-yyyy.');
          return;
        }
        body.append('expiry_date', expiry);
      }

      // Generate base route (with :id placeholder)
      let url = @json(route('documents.link.create', ['id' => ':id']));
      url = url.replace(':id', id);

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body
      });
      const data = await res.json();
      document.getElementById('result').textContent = JSON.stringify(data, null, 2);
    });
  </script>

</body>
</html>
