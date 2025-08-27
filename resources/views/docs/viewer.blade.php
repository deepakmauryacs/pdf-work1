<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>PDF Viewer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <style>
    html, body { height:100%; }
    #viewer-container { height:100%; overflow:auto; }
  </style>
</head>
<body class="container-fluid h-100 py-3">
  <div class="row h-100">
    <div class="col-md-12 h-100">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="btn-group" role="group">
            <button id="prevBtn" class="btn btn-sm btn-secondary">&laquo;</button>
            <button id="nextBtn" class="btn btn-sm btn-secondary">&raquo;</button>
            <button id="zoomOutBtn" class="btn btn-sm btn-secondary">-</button>
            <button id="zoomInBtn" class="btn btn-sm btn-secondary">+</button>
          </div>
          <div class="d-flex align-items-center">
            <span id="pageIndicator" class="me-3 small text-muted"></span>
            @if($allowDownload)
              <button id="downloadBtn" class="btn btn-sm btn-primary">Download</button>
            @endif
          </div>
        </div>
        <div id="viewer-container" class="h-100 d-flex align-items-center justify-content-center">
          <canvas id="pdfCanvas" class="d-block"></canvas>
        </div>
      </div>
    </div>
  </div>

<script>
window.PDF_VIEWER = {
  streamRoute: '{{ $streamRoute }}',
  downloadRoute: '{{ $downloadRoute }}',
  slug: '{{ $slug }}',
  nonce: '{{ $nonce }}',
  csrf: '{{ csrf_token() }}',
  allowDownload: @json($allowDownload),
  filename: '{{ $doc->original_name }}'
};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
<script src="/js/pdf-viewer.js"></script>
</body>
</html>
