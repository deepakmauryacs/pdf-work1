(async () => {
  try {
    const res = await fetch(window.PDF_VIEWER.streamRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': window.PDF_VIEWER.csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({ s: window.PDF_VIEWER.slug, nonce: window.PDF_VIEWER.nonce })
    });
    if (!res.ok) throw new Error('Unable to load PDF');
    const blob = await res.blob();
    const blobUrl = URL.createObjectURL(blob);
    const iframe = document.createElement('iframe');
    iframe.src = blobUrl;
    iframe.className = 'w-100 h-100 border-0';
    document.getElementById('viewer-container').appendChild(iframe);
  } catch(err) {
    alert(err.message || 'Error loading document');
  }

  if (window.PDF_VIEWER.allowDownload) {
    document.getElementById('downloadBtn').addEventListener('click', async () => {
      const res = await fetch(window.PDF_VIEWER.downloadRoute, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': window.PDF_VIEWER.csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ s: window.PDF_VIEWER.slug })
      });
      if (!res.ok) { alert('Download failed'); return; }
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = window.PDF_VIEWER.filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    });
  }
})();

