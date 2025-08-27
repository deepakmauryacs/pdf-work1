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
