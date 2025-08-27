{{-- resources/views/docs/viewer.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>{{ $doc->original_name }} — Viewer</title>

  {{-- Fonts + Icons --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>

  <style>
    :root{
      --bg:#0b1220; --panel:#0f172a; --muted:#94a3b8; --text:#e2e8f0; --accent:#22d3ee;
      --border:#132038; --btn:#1b2947; --btn-h:#233356; --ring:#22d3ee; --good:#16a34a; --warn:#f59e0b; --danger:#ef4444;
      --thumb-bg:#0b162d; --thumb-br:#233154;
    }
    .theme--light{
      --bg:#f7fafc; --panel:#ffffff; --muted:#5b6b86; --text:#0f172a; --accent:#0ea5e9;
      --border:#e6ebf2; --btn:#f1f5f9; --btn-h:#e2e8f0; --ring:#0ea5e9;
      --thumb-bg:#f1f5f9; --thumb-br:#e2e8f0;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; background:var(--bg); color:var(--text);
      font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      font-size:14px; line-height:1.45;
    }

    /* ====== PRO TOPBAR (glass + segmented controls) ====== */
    .topbar { position: sticky; top: 12px; z-index: 60; margin: 0 12px 12px; }
    .topbar__shell{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding: 10px 14px; border-radius: 14px;
      background: color-mix(in oklab, var(--panel) 80%, transparent);
      backdrop-filter: blur(12px) saturate(120%);
      border:1px solid var(--border);
      box-shadow: 0 6px 20px rgba(0,0,0,.25), inset 0 1px 0 rgba(255,255,255,.04);
    }
    .brand{display:flex; align-items:center; gap:10px; min-width:0}
    .brand .bi{font-size:20px; opacity:.85}
    .filemeta{min-width:0}
    .filemeta__title{
      font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:38vw;
    }
    .filemeta__sub{ display:flex; align-items:center; gap:6px; font-size:12px; color:var(--muted); }

    .controls{display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end}
    .seg{ display:inline-flex; align-items:center; gap:0; background:var(--btn); border:1px solid var(--border); border-radius:12px; padding:2px; }
    .seg .btn{ background:transparent; border:0; padding:8px 10px; border-radius:10px; color:var(--text); cursor:pointer; }
    .seg .btn:hover{ background:var(--btn-h) }
    .seg .btn + .btn{ margin-left:2px }

    .cta{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 14px; border-radius:12px; font-weight:600;
      background: linear-gradient(180deg, #3b82f6, #1e40af);
      color:#fff; border:1px solid #1e3a8a; box-shadow:0 4px 14px rgba(59,130,246,.35);
      cursor:pointer;
    }
    .cta[disabled]{opacity:.55; cursor:not-allowed}
    .cta .bi{font-size:16px}

    .input--search{
      height:36px; width:260px; padding-left:34px; padding-right:10px;
      border-radius:10px; border:1px solid var(--border); background:transparent; color:var(--text); outline:none;
    }
    .input-wrap{position:relative}
    .input-wrap .bi{position:absolute; left:10px; top:50%; transform:translateY(-50%); opacity:.75}
    .input--num{ height:36px; width:80px; text-align:center; border-radius:10px; border:1px solid var(--border); background:transparent; color:var(--text); }

    /* ====== Body layout ====== */
    .viewer-shell{display:grid; grid-template-rows:auto 1fr; height:100vh}
    .shell{display:grid; grid-template-columns:280px 1fr; min-height:0}
    .side{background:var(--panel); border-right:1px solid var(--border); min-width:220px; max-width:360px; overflow:auto}
    .side__head{
      display:flex; align-items:center; gap:8px; padding:10px 12px; border-bottom:1px solid var(--border);
      position:sticky; top:0; background:var(--panel); z-index:5;
    }
    .side__list{display:grid; gap:10px; padding:12px}
    .thumb{display:grid; gap:6px; padding:10px; background:var(--thumb-bg); border:1px solid var(--thumb-br);
      border-radius:12px; cursor:pointer; transition:transform .12s ease, border-color .12s ease;}
    .thumb:hover{transform:translateY(-1px)}
    .thumb--active{outline:2px solid var(--accent); outline-offset:2px; border-color:transparent}
    .thumb__canvas{width:100%; background:#0a0f1e; border-radius:8px}
    .thumb__meta{display:flex; justify-content:space-between; color:var(--muted); font-size:12px}

    .main{display:grid; grid-template-rows:1fr auto; min-width:0}
    .canvas-wrap{display:grid; place-items:center; overflow:auto; padding:14px}
    canvas#pdfCanvas{
      background:#0a0f1e; border-radius:14px; box-shadow:0 0 0 1px var(--border), inset 0 0 80px rgba(0,0,0,.18);
      max-width:100%;
    }

    .bottombar{
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      padding:10px 14px; background:var(--panel); border-top:1px solid var(--border);
    }
    .progress{height:8px; background:var(--border); border-radius:999px; overflow:hidden; width:240px}
    .progress__bar{height:100%; background:linear-gradient(90deg,var(--accent),#60a5fa); width:0%}
    .badge{font-size:12px; color:var(--muted)}

    /* Responsive */
    @media (max-width: 992px){
      .filemeta__title{max-width:40vw}
      .input--search{width:180px}
      .shell{grid-template-columns:0 1fr}
      .side{position:fixed; inset:80px 0 0 0; max-width:none; transform:translateX(-100%); transition:transform .2s ease}
      .side--open{transform:translateX(0)}
    }
    @media (max-width: 640px){
      .topbar{top:0; margin:0}
      .topbar__shell{flex-direction:column; align-items:stretch; gap:10px; border-radius:0}
      .controls{justify-content:space-between}
      .filemeta__title{max-width:80vw}
      .canvas-wrap{padding:0}
      canvas#pdfCanvas{border-radius:0; box-shadow:none}
    }
  </style>
</head>
<body class="viewer theme--dark" oncontextmenu="event.preventDefault();">
<div class="viewer-shell">

  {{-- TOPBAR (Glass, premium) --}}
  <div class="topbar">
    <div class="topbar__shell">
      <div class="brand">
        <i class="bi bi-file-earmark-text"></i>
        <div class="filemeta">
          <div class="filemeta__title">{{ $doc->original_name }}</div>
          <div class="filemeta__sub">
            <span id="pageLabel">Page 1</span>
            <span>•</span>
            <span id="pageCount">/ ?</span>
          </div>
        </div>
      </div>

      <div class="controls">
        <div class="seg">
          <button class="btn" id="toggleSide" title="Thumbnails"><i class="bi bi-images"></i></button>
        </div>

        <div class="input-wrap">
          <i class="bi bi-search"></i>
          <input id="q" class="input--search" placeholder="Search in document…">
        </div>
        <div class="seg">
          <button class="btn" id="qPrev" title="Prev match"><i class="bi bi-chevron-up"></i></button>
          <button class="btn" id="qNext" title="Next match"><i class="bi bi-chevron-down"></i></button>
        </div>
        <span id="qCount" class="filemeta__sub"></span>

        <div class="seg">
          <button class="btn" id="rotate"   title="Rotate"><i class="bi bi-arrow-clockwise"></i></button>
          <button class="btn" id="fit"      title="Fit width"><i class="bi bi-arrows"></i></button>
          <button class="btn" id="zoomOut"  title="Zoom out"><i class="bi bi-zoom-out"></i></button>
          <button class="btn" id="zoomIn"   title="Zoom in"><i class="bi bi-zoom-in"></i></button>
          <button class="btn" id="fs"       title="Full screen"><i class="bi bi-arrows-fullscreen"></i></button>
        </div>

        <input id="pageNum" class="input--num" type="number" value="1" min="1"/>

        @if($allowDownload)
          <form id="dlForm" method="post" action="{{ $downloadRoute }}" style="display:inline;">
            @csrf
            <input type="hidden" name="s" value="{{ $slug }}">
            <button type="submit" class="cta" title="Download">
              <i class="bi bi-download"></i> Download
            </button>
          </form>
        @else
          <button class="cta" disabled title="Download disabled">
            <i class="bi bi-download"></i> Download
          </button>
        @endif

        <div class="seg">
          <button class="btn" id="themeDark"  title="Dark"><i class="bi bi-moon-stars"></i></button>
          <button class="btn" id="themeLight" title="Light"><i class="bi bi-brightness-high"></i></button>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="shell">
    {{-- LEFT: Thumbnails --}}
    <aside id="side" class="side">
      <div class="side__head">
        <i class="bi bi-images"></i>
        <strong>Thumbnails</strong>
      </div>
      <div id="thumbList" class="side__list"></div>
    </aside>

    {{-- RIGHT: Main canvas --}}
    <main class="main">
      <div id="canvasWrap" class="canvas-wrap">
        <canvas id="pdfCanvas"></canvas>
      </div>
      <div class="bottombar">
        <div class="badge">Tips: ←/→ page • +/- zoom • F full-screen • / search</div>
        <div class="progress" aria-label="Progress">
          <div id="prog" class="progress__bar"></div>
        </div>
      </div>
    </main>
  </div>
</div>

{{-- pdf.js core (no viewer.html) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

<script>
(function(){
  const csrf   = document.querySelector('meta[name="csrf-token"]').content;
  const postUrl= @json($streamRoute);   // POST-only endpoint
  const slug   = @json($slug);
  const nonce  = @json($nonce);

  // Elements
  const wrap = document.getElementById('canvasWrap');
  const canvas = document.getElementById('pdfCanvas');
  const ctx = canvas.getContext('2d', { alpha:false, desynchronized:true });

  const pageNumEl = document.getElementById('pageNum');
  const pageCountEl = document.getElementById('pageCount');
  const pageLabel = document.getElementById('pageLabel');

  const zoomInBtn = document.getElementById('zoomIn');
  const zoomOutBtn = document.getElementById('zoomOut');
  const fitBtn = document.getElementById('fit');
  const rotateBtn = document.getElementById('rotate');
  const fsBtn = document.getElementById('fs');

  const themeDarkBtn  = document.getElementById('themeDark');
  const themeLightBtn = document.getElementById('themeLight');
  const prog = document.getElementById('prog');

  const side = document.getElementById('side');
  const sideToggle = document.getElementById('toggleSide');

  // Search elements
  const qInput = document.getElementById('q');
  const qPrev  = document.getElementById('qPrev');
  const qNext  = document.getElementById('qNext');
  const qCount = document.getElementById('qCount');

  // State
  let pdfDoc = null, pageNum = 1, scale = 1.15, rotation = 0, rendering = false, pending = null;
  let pageTexts = [];     // cache of page text
  let searchHits = [];    // [{page, index}]
  let hitIndex = -1;

  const clamp = (n,min,max)=>Math.max(min,Math.min(max,n));
  const dpr = () => (window.devicePixelRatio || 1);

  function setProgress(){
    if (!pdfDoc) return;
    const pct = (pageNum - 1) / (pdfDoc.numPages - 1 || 1) * 100;
    prog.style.width = pct.toFixed(2) + '%';
  }
  function updateUI(){
    if (!pdfDoc) return;
    pageNumEl.value = pageNum;
    pageCountEl.textContent = '/ ' + pdfDoc.numPages;
    pageLabel.textContent = 'Page ' + pageNum;
    setProgress();
    document.querySelectorAll('.thumb').forEach(el=>{
      const p = Number(el.dataset.page);
      el.classList.toggle('thumb--active', p === pageNum);
    });
  }
  function fitWidth(page){
    const view = page.getViewport({ scale: 1, rotation });
    const w = wrap.clientWidth - 24;
    scale = clamp(w / view.width, 0.4, 4);
  }

  async function renderPage(num){
    rendering = true;
    const page = await pdfDoc.getPage(num);
    if (renderPage._fitOnce) { fitWidth(page); renderPage._fitOnce = false; }

    const viewport = page.getViewport({ scale, rotation });
    const ratio = dpr();
    canvas.width  = Math.floor(viewport.width  * ratio);
    canvas.height = Math.floor(viewport.height * ratio);
    canvas.style.width  = Math.floor(viewport.width) + 'px';
    canvas.style.height = Math.floor(viewport.height) + 'px';
    ctx.setTransform(ratio,0,0,ratio,0,0);

    const task = page.render({ canvasContext: ctx, viewport });
    await task.promise;
    rendering = false;
    if (pending !== null){ const n = pending; pending = null; renderPage(n); }
    updateUI();
  }
  function queueRender(n){ if(rendering) pending=n; else renderPage(n); }

  // Thumbnails
  async function buildThumbnails(){
    const list = document.getElementById('thumbList');
    list.innerHTML = '';
    for (let p=1; p<=pdfDoc.numPages; p++){
      const item = document.createElement('div'); item.className='thumb'; item.dataset.page=String(p);
      const c = document.createElement('canvas'); c.className='thumb__canvas'; c.width=180; c.height=240;
      const meta = document.createElement('div'); meta.className='thumb__meta';
      meta.innerHTML = `<span>Page ${p}</span><span class="bi bi-chevron-right"></span>`;
      item.appendChild(c); item.appendChild(meta); list.appendChild(item);

      // render tiny preview
      pdfDoc.getPage(p).then(page=>{
        const vp = page.getViewport({ scale: 0.25 });
        const r = Math.min(180 / vp.width, 240 / vp.height);
        const v2 = page.getViewport({ scale: 0.25 * r });
        const tctx = c.getContext('2d');
        c.width = Math.floor(v2.width); c.height = Math.floor(v2.height);
        page.render({ canvasContext: tctx, viewport: v2 });
      });

      item.addEventListener('click', ()=>{ pageNum = p; queueRender(pageNum); });
    }
  }

  // Search
  async function ensurePageText(p){
    if (pageTexts[p]) return pageTexts[p];
    const page = await pdfDoc.getPage(p);
    const tc = await page.getTextContent();
    const text = tc.items.map(x=>x.str).join(' ');
    pageTexts[p] = text;
    return text;
  }
  async function runSearch(q){
    searchHits = []; hitIndex = -1;
    qCount.textContent = '';
    if (!q || q.trim().length < 2) return;

    for(let p=1; p<=pdfDoc.numPages; p++){
      const t = (await ensurePageText(p)).toLowerCase();
      const needle = q.toLowerCase();
      let idx = t.indexOf(needle), seen=0;
      while(idx !== -1 && seen < 30){
        searchHits.push({page:p, index:idx});
        idx = t.indexOf(needle, idx+needle.length);
        seen++;
      }
    }
    if (searchHits.length){
      hitIndex = 0;
      pageNum = searchHits[0].page; queueRender(pageNum);
      qCount.textContent = `${hitIndex+1}/${searchHits.length}`;
    }else{
      qCount.textContent = '0/0';
    }
  }
  function navSearch(delta){
    if (!searchHits.length) return;
    hitIndex = (hitIndex + delta + searchHits.length) % searchHits.length;
    pageNum = searchHits[hitIndex].page; queueRender(pageNum);
    qCount.textContent = `${hitIndex+1}/${searchHits.length}`;
  }

  // Fetch PDF bytes via POST (no URL exposure)
  async function loadBytes(){
    const res = await fetch(postUrl, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: (()=>{ const fd = new FormData(); fd.append('s', slug); fd.append('nonce', nonce); return fd; })()
    });
    if (!res.ok) throw new Error('Failed to load PDF');
    return await res.arrayBuffer();
  }

  // Controls
  zoomInBtn.onclick = ()=>{ scale = clamp(scale + 0.15, 0.4, 6); queueRender(pageNum); };
  zoomOutBtn.onclick= ()=>{ scale = clamp(scale - 0.15, 0.3, 6); queueRender(pageNum); };
  fitBtn.onclick    = ()=>{ renderPage._fitOnce = true; queueRender(pageNum); };
  rotateBtn.onclick = ()=>{ rotation = (rotation + 90) % 360; queueRender(pageNum); };
  fsBtn.onclick     = ()=>{ const el=document.documentElement; if(!document.fullscreenElement){ el.requestFullscreen?.(); } else { document.exitFullscreen?.(); } };
  pageNumEl.onchange= (e)=>{ const v = clamp(parseInt(e.target.value||'1',10),1,pdfDoc.numPages); pageNum=v; queueRender(pageNum); };

  // Theme buttons
  themeDarkBtn.onclick  = ()=>{ document.body.classList.add('theme--dark');  document.body.classList.remove('theme--light'); };
  themeLightBtn.onclick = ()=>{ document.body.classList.add('theme--light'); document.body.classList.remove('theme--dark'); };

  // Sidebar toggle (mobile)
  sideToggle?.addEventListener('click', ()=> side.classList.toggle('side--open'));

  // Keyboard
  document.addEventListener('keydown', (e)=>{
    const tag = (document.activeElement?.tagName||'').toUpperCase();
    const typing = (tag === 'INPUT' || tag === 'TEXTAREA');
    if (!typing && (e.key==='ArrowRight' || e.key==='PageDown')) { if(pageNum<pdfDoc.numPages){ pageNum++; queueRender(pageNum);} }
    if (!typing && (e.key==='ArrowLeft'  || e.key==='PageUp'))   { if(pageNum>1){ pageNum--; queueRender(pageNum);} }
    if (!typing && e.key==='+') zoomInBtn.click();
    if (!typing && e.key==='-') zoomOutBtn.click();
    if (!typing && e.key.toLowerCase()==='f') fsBtn.click();
    if (e.key === '/') { e.preventDefault(); qInput.focus(); }
  });

  // Search events
  let searchTimer=null;
  qInput.addEventListener('input', ()=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>runSearch(qInput.value), 250);
  });
  qPrev.onclick = ()=> navSearch(-1);
  qNext.onclick = ()=> navSearch(1);

  // Resize → refit (debounced)
  let rzTimer=null;
  window.addEventListener('resize', ()=>{
    clearTimeout(rzTimer);
    rzTimer=setTimeout(()=>{ renderPage._fitOnce = true; queueRender(pageNum); }, 150);
  });

  // Init
  (async ()=>{
    try{
      const buf = await loadBytes();
      pdfDoc = await pdfjsLib.getDocument({ data: buf }).promise;
      await buildThumbnails();
      renderPage._fitOnce = true;
      await renderPage(pageNum);
      updateUI();
    }catch(err){
      alert('Unable to open document.');
      console.error(err);
    }
  })();
})();
</script>
</body>
</html>
