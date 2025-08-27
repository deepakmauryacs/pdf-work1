(async () => {
  const cfg = window.PDF_VIEWER;
  let pdf, currentPage = 1, scale = 1;
  const canvas = document.getElementById('pdfCanvas');
  const ctx = canvas.getContext('2d');
  const indicator = document.getElementById('pageIndicator');

  const renderPage = async (num) => {
    const page = await pdf.getPage(num);
    const viewport = page.getViewport({ scale });
    canvas.height = viewport.height;
    canvas.width = viewport.width;
    await page.render({ canvasContext: ctx, viewport }).promise;
    indicator.textContent = `${num} / ${pdf.numPages}`;
  };

  try {
    const res = await fetch(cfg.streamRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': cfg.csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({ s: cfg.slug, nonce: cfg.nonce })
    });
    if (!res.ok) throw new Error('Unable to load PDF');
    const buffer = await res.arrayBuffer();
    pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
    await renderPage(currentPage);
  } catch (err) {
    alert(err.message || 'Error loading document');
  }

  document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentPage <= 1) return;
    currentPage--;
    renderPage(currentPage);
  });
  document.getElementById('nextBtn').addEventListener('click', () => {
    if (!pdf || currentPage >= pdf.numPages) return;
    currentPage++;
    renderPage(currentPage);
  });
  document.getElementById('zoomInBtn').addEventListener('click', () => {
    scale = Math.min(scale + 0.25, 3);
    renderPage(currentPage);
  });
  document.getElementById('zoomOutBtn').addEventListener('click', () => {
    scale = Math.max(scale - 0.25, 0.5);
    renderPage(currentPage);
  });

  if (cfg.allowDownload) {
    document.getElementById('downloadBtn').addEventListener('click', async () => {
      const res = await fetch(cfg.downloadRoute, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': cfg.csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ s: cfg.slug })
      });
      if (!res.ok) { alert('Download failed'); return; }
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = cfg.filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    });
  }
})();

