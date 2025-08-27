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
    const buffer = await res.arrayBuffer();
    const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
    const container = document.getElementById('viewer-container');
    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
      const page = await pdf.getPage(pageNum);
      const viewport = page.getViewport({ scale: 1 });
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');
      canvas.height = viewport.height;
      canvas.width = viewport.width;
      canvas.className = 'd-block mb-3 mx-auto';
      container.appendChild(canvas);
      await page.render({ canvasContext: context, viewport }).promise;
    }
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

