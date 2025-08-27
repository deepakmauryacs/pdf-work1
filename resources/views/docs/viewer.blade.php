<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>PDF Viewer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <style>
    html, body { height:100%; }
    #viewer-container { height:100%; }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/@accusoft/pdf-viewer@3/bundle.js"></script>
</head>
<body class="container-fluid h-100 py-3">
  <div class="row h-100">
    <div class="col-md-12 h-100">
      <div class="card h-100">
        @if($allowDownload)
        <div class="card-header d-flex justify-content-end">
          <button id="downloadBtn" class="btn btn-sm btn-primary">Download</button>
        </div>
        @endif
        <div id="viewer-container" class="h-100"></div>
      </div>
    </div>
  </div>

<script>
(async () => {
  try {
    const res = await fetch('{{ $streamRoute }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({ s: '{{ $slug }}', nonce: '{{ $nonce }}' })
    });
    if (!res.ok) throw new Error('Unable to load PDF');
    const blob = await res.blob();
    const blobUrl = URL.createObjectURL(blob);

    await window.Accusoft.PdfViewerControl.create({
      sourceDocument: blobUrl,
      container: document.getElementById('viewer-container'),
      licenseKey: 'eval',
      allowedControls: {
        annotationList: true,
        ellipseTool: true,
        freehandDrawingTool: true,
        freehandSignatureTool: true,
        lineTool: true,
        outline: true,
        rectangleTool: true,
        textHighlightTool: true,
        fullScreenToggle: true,
        nextAndPreviousPageButtons: true,
        pageNumberAndCount: true,
        printing: true,
        search: true,
        thumbnails: true,
        zoomInAndOutButtons: true
      }
    });
  } catch(err) {
    alert(err.message || 'Error loading document');
  }

  @if($allowDownload)
  document.getElementById('downloadBtn').addEventListener('click', async () => {
    const res = await fetch('{{ $downloadRoute }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({ s: '{{ $slug }}' })
    });
    if (!res.ok) { alert('Download failed'); return; }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '{{ $doc->original_name }}';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  });
  @endif
})();
</script>
</body>
</html>
