<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/auth_mock.php';
license_check();
$userId = current_user_id();
$docs = db_all("SELECT * FROM documents WHERE user_id=? ORDER BY id DESC", [$userId]);
?>
<!doctype html><html><head>
<meta charset="utf-8"><title>PDF Library</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head><body class="p-4">
<div class="row"><div class="col-md-12"><div class="card p-4">
<h3>My PDFs</h3>
<form id="uploadForm" class="my-3" action="upload.php" method="post" enctype="multipart/form-data">
  <input type="file" name="pdf" accept="application/pdf" required>
  <button class="btn btn-primary">Upload (max 50MB)</button>
</form>

<div id="linkResult" class="mb-3"></div>

<table class="table table-bordered align-middle">
  <thead><tr><th>PDF Name</th><th>Size</th><th>Uploaded</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach($docs as $d): 
        $ts = (int)explode('_', $d['filename'])[0];
        $uploaded = date('d-m-Y', $ts);
  ?>
    <tr>
      <td><?= htmlspecialchars($d['original_name']) ?></td>
      <td><?= humanSize($d['size_bytes']) ?></td>
      <td><?= $uploaded ?></td>
      <td>
        <form class="d-inline ajax-form" action="link_create.php" method="post">
          <input type="hidden" name="doc_id" value="<?= $d['id'] ?>">
          <input type="hidden" name="kind" value="view">
          <div class="d-inline-flex gap-2 align-items-center">
            <label class="form-check-label">View
              <input class="form-check-input ms-1" type="checkbox" name="allow_view" checked>
            </label>
            <label class="form-check-label">Download
              <input class="form-check-input ms-1" type="checkbox" name="allow_download">
            </label>
            <label class="form-check-label">Search
              <input class="form-check-input ms-1" type="checkbox" name="allow_search" checked>
            </label>
            <button class="btn btn-sm btn-dark">GENERATE LINK</button>
          </div>
        </form>

        <form class="d-inline ms-2 ajax-form" action="link_create.php" method="post">
          <input type="hidden" name="doc_id" value="<?= $d['id'] ?>">
          <input type="hidden" name="kind" value="embed">
          <div class="d-inline-flex gap-2 align-items-center">
            <label class="form-check-label">View
              <input class="form-check-input ms-1" type="checkbox" name="allow_view" checked>
            </label>
            <label class="form-check-label">Download
              <input class="form-check-input ms-1" type="checkbox" name="allow_download">
            </label>
            <label class="form-check-label">Search
              <input class="form-check-input ms-1" type="checkbox" name="allow_search" checked>
            </label>
            <button class="btn btn-sm btn-secondary">EMBEDDED WEBSITE</button>
          </div>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div></div>

<script>
// upload form via AJAX with basic client-side validation
document.getElementById('uploadForm').addEventListener('submit', async function(e){
  e.preventDefault();
  if(!this.pdf.files.length){ alert('Please select a PDF file.'); return; }
  const file=this.pdf.files[0];
  if(file.type!=='application/pdf'){ alert('Only PDF allowed'); return; }
  if(file.size>50*1024*1024){ alert('File too large (>50MB)'); return; }
  const fd=new FormData(this);
  const resp=await fetch(this.action,{method:'POST',body:fd});
  let data=null;
  try{ data=await resp.json(); }catch{}
  if(resp.ok && data && data.ok){ location.reload(); }
  else{ alert(data?.error || 'Upload failed'); }
});

// link creation forms via AJAX
document.querySelectorAll('.ajax-form').forEach(f=>{
  f.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd=new FormData(f);
    if(!/^\d+$/.test(fd.get('doc_id'))){ alert('Invalid document'); return; }
    const resp=await fetch(f.action,{method:'POST',body:fd});
    let data=null;
    try{ data=await resp.json(); }catch{}
    if(!resp.ok || data.error){ alert(data?.error || 'Server error'); return; }
    let html='<p><a href="'+data.url+'" target="_blank">'+data.url+'</a></p>';
    if(data.embed){ html+='<textarea class="form-control">'+data.embed+'</textarea>'; }
    document.getElementById('linkResult').innerHTML=html;
  });
});
</script>
</body></html>
