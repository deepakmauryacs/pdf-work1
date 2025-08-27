<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $doc->original_name }} — Premium Viewer</title>

    {{-- Fonts + Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>

    <style>
        :root{
            --bg:#0b1220; --panel:#0f172a; --muted:#94a3b8; --text:#e2e8f0; --accent:#22d3ee;
            --border:#132038; --btn:#1b2947; --btn-h:#233356; --ring:#22d3ee; --good:#16a34a; --warn:#f59e0b; --danger:#ef4444;
            --thumb-bg:#0b162d; --thumb-br:#233154; --glass:rgba(15, 23, 42, 0.85);
            --transition: all 0.2s ease;
        }
        .theme--light{
            --bg:#f7fafc; --panel:#ffffff; --muted:#5b6b86; --text:#0f172a; --accent:#0ea5e9;
            --border:#e6ebf2; --btn:#f1f5f9; --btn-h:#e2e8f0; --ring:#0ea5e9; --glass:rgba(255, 255, 255, 0.85);
            --thumb-bg:#f1f5f9; --thumb-br:#e2e8f0;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; background:var(--bg); color:var(--text);
            font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            font-size:14px; line-height:1.5;
            overflow: hidden;
        }

        /* ====== PRO TOPBAR (glass + segmented controls) ====== */
        .topbar {
            position: sticky;
            top: 16px;
            z-index: 100;
            margin: 0 16px 16px;
        }
        .topbar__shell{
            display:flex; align-items:center; justify-content:space-between; gap:16px;
            padding: 12px 18px; border-radius: 16px;
            background: var(--glass);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0,0,0,.3), 0 2px 8px rgba(0,0,0,.1), inset 0 1px 0 rgba(255,255,255,.05);
            transition: var(--transition);
        }
        .brand{display:flex; align-items:center; gap:12px; min-width:0}
        .brand .bi{font-size:22px; color: var(--accent);}
        .filemeta{min-width:0}
        .filemeta__title{
            font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:38vw;
            font-size: 15px;
        }
        .filemeta__sub{
            display:flex; align-items:center; gap:6px; font-size:12px; color:var(--muted);
            margin-top: 2px;
        }

        .controls{display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end}
        .seg{
            display:inline-flex; align-items:center; gap:0; background:var(--btn);
            border:1px solid var(--border); border-radius:12px; padding:2px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
        }
        .seg .btn{
            background:transparent; border:0; padding:8px 12px; border-radius:10px;
            color:var(--text); cursor:pointer; transition: var(--transition);
            display: flex; align-items: center; justify-content: center;
        }
        .seg .btn:hover{ background:var(--btn-h); transform: translateY(-1px); }
        .seg .btn:active{ transform: translateY(0); }
        .seg .btn + .btn{ margin-left:2px }
        .seg .btn .bi{ font-size: 16px; }

        .cta{
            display:inline-flex; align-items:center; gap:8px;
            padding:10px 16px; border-radius:12px; font-weight:600;
            background: linear-gradient(180deg, #3b82f6, #1e40af);
            color:#fff; border:1px solid #1e3a8a; box-shadow:0 4px 16px rgba(59,130,246,.4);
            cursor:pointer; transition: var(--transition);
        }
        .cta:hover {
            transform: translateY(-2px);
            box-shadow:0 6px 20px rgba(59,130,246,.5);
        }
        .cta:active {
            transform: translateY(0);
        }
        .cta[disabled]{opacity:.55; cursor:not-allowed; transform: none !important;}
        .cta .bi{font-size:16px}

        .input--search{
            height:38px; width:260px; padding-left:38px; padding-right:12px;
            border-radius:10px; border:1px solid var(--border); background:var(--btn); color:var(--text); outline:none;
            transition: var(--transition); font-size: 13px;
        }
        .input--search:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
        }
        .input-wrap{position:relative}
        .input-wrap .bi{position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.75}
        .input--num{
            height:38px; width:80px; text-align:center; border-radius:10px;
            border:1px solid var(--border); background:var(--btn); color:var(--text);
            font-weight: 500;
        }
        .input--num:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
        }

        /* ====== Body layout ====== */
        .viewer-shell{
            display:grid;
            grid-template-rows: auto 1fr;
            height:100vh;
            overflow: hidden;
        }
        .shell{
            display:grid;
            grid-template-columns: 300px 1fr;
            min-height:0;
            overflow: hidden;
        }
        .side{
            background:var(--panel);
            border-right:1px solid var(--border);
            min-width:220px;
            max-width:360px;
            overflow:auto;
            display: flex;
            flex-direction: column;
        }
        .side__head{
            display:flex; align-items:center; gap:10px; padding:14px 16px; border-bottom:1px solid var(--border);
            position:sticky; top:0; background:var(--panel); z-index:5;
        }
        .side__head strong {
            font-weight: 600;
        }
        .side__list{
            display:grid;
            gap:12px;
            padding:16px;
            flex: 1;
            overflow-y: auto;
        }
        .thumb{
            display:grid;
            gap:8px;
            padding:12px;
            background:var(--thumb-bg);
            border:1px solid var(--thumb-br);
            border-radius:12px;
            cursor:pointer;
            transition: var(--transition);
        }
        .thumb:hover{
            transform:translateY(-2px);
            border-color: color-mix(in srgb, var(--accent) 30%, transparent);
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        .thumb--active{
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 10%, var(--thumb-bg));
            box-shadow: 0 0 0 1px var(--accent), 0 4px 12px rgba(34, 211, 238, 0.15);
        }
        .thumb__canvas{
            width:100%;
            background:#0a0f1e;
            border-radius:8px;
            transition: var(--transition);
        }
        .thumb__meta{
            display:flex;
            justify-content:space-between;
            color:var(--muted);
            font-size:12px;
            font-weight: 500;
        }

        .main{
            display:grid;
            grid-template-rows:1fr auto;
            min-width:0;
            min-height:0;
            position: relative;
        }
        .canvas-wrap{
            overflow:auto;
            padding:20px;
            position: relative;
            height:100%;
        }
        #pdfContainer{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:20px;
        }
        #pdfContainer canvas{
            background:#0a0f1e;
            border-radius:16px;
            box-shadow:0 0 0 1px var(--border), 0 8px 30px rgba(0,0,0,.3);
            max-width:100%;
            transition: var(--transition);
        }

        .bottombar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:12px 20px;
            background:var(--panel);
            border-top:1px solid var(--border);
        }
        .progress{
            height:6px;
            background:var(--border);
            border-radius:999px;
            overflow:hidden;
            width:240px;
            flex: 1;
            max-width: 400px;
        }
        .progress__bar{
            height:100%;
            background:linear-gradient(90deg,var(--accent),#60a5fa);
            width:0%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .badge{
            font-size:12px;
            color:var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .badge .bi {
            font-size: 14px;
        }

        /* Loading state */
        .loader {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }
        .loader__spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--border);
            border-radius: 50%;
            border-top-color: var(--accent);
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Search highlight */
        .highlight {
            background-color: rgba(255, 0, 0, 0.4); /* Changed to Red */
            border-radius: 2px;
            transition: background-color 0.2s ease;
            position: absolute;
            z-index: 2;
            opacity: 1;
        }
        .highlight--active {
            background-color: rgba(255, 0, 0, 0.8); /* Changed to Red */
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.5); /* Changed to Red */
        }
        .text-layer {
            position: absolute;
            inset: 0;
            overflow: hidden;
            z-index: 1;
        }

        /* Document info panel */
        .doc-info {
            border-top: 1px solid var(--border);
            padding: 14px 16px;
            font-size: 13px;
            color: var(--muted);
        }
        .doc-info__item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .doc-info__label {
            font-weight: 500;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 16px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        .toast--visible {
            transform: translateY(0);
            opacity: 1;
        }
        .toast__icon {
            font-size: 18px;
        }
        .toast__message {
            font-size: 14px;
            font-weight: 500;
        }
        .toast--success .toast__icon {
            color: var(--good);
        }
        .toast--error .toast__icon {
            color: var(--danger);
        }

        /* Responsive */
        @media (max-width: 1200px){
            .filemeta__title{max-width:30vw}
        }
        @media (max-width: 992px){
            /* Main body layout change */
            .shell{grid-template-columns:0 1fr}
            .side{
                position:fixed; inset:0; max-width:none;
                transform:translateX(-100%); transition:transform .3s ease;
                z-index: 50; width: 320px;
            }
            .side--open{transform:translateX(0)}
            .side-overlay {
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5); z-index: 40;
                display: none;
            }
            .side-overlay--visible {
                display: block;
            }

            /* Topbar adjustments */
            .topbar {
                top: 12px; margin: 0 12px 12px;
            }
            .topbar__shell{
                flex-direction:column; align-items:stretch;
                gap:12px; padding: 12px;
            }
            .filemeta__title{max-width:100%; font-size: 14px;}
            .controls{
                justify-content:space-between; flex-wrap: wrap; gap: 8px;
            }
            .input--search{width: 100%;}
            .seg:first-child {margin-right: auto;}
            .cta {order: -1;}
            .seg:last-child {order: 1; flex-grow: 1; justify-content: space-around;}
        }

        @media (max-width: 768px){
            /* Bottom bar on smaller screens */
            .bottombar {
                flex-direction: column; align-items: center; gap: 10px;
                padding: 10px;
            }
            .badge {display: none;}
            .progress {width: 100%;}
            .input--search {width: 100%;}
        }

        @media (max-width: 640px){
            .controls{
                flex-wrap: wrap; justify-content: center;
                gap: 8px;
            }
            .cta span {display: none;}
            .cta {padding: 10px;}
            .input--search {width: 100%;}
            .seg:not(:first-child) {
                flex-grow: 1; justify-content: space-around;
            }
        }

        /* Fullscreen styles */
        body:fullscreen .topbar,
        body:-webkit-full-screen .topbar,
        body:-moz-full-screen .topbar,
        body:-ms-fullscreen .topbar {
            top: 0; margin: 12px;
        }
        body:fullscreen .topbar__shell,
        body:-webkit-full-screen .topbar__shell,
        body:-moz-full-screen .topbar__shell,
        body:-ms-fullscreen .topbar__shell {
            backdrop-filter: blur(24px) saturate(180%);
        }
    </style>
</head>
<body class="viewer theme--dark" oncontextmenu="event.preventDefault();">
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="viewer-shell">

                {{-- TOPBAR (Glass, premium) --}}
                <div class="topbar">
                    <div class="topbar__shell">
                        <div class="brand">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <div class="filemeta">
                                <div class="filemeta__title">{{ $doc->original_name }}</div>
                                <div class="filemeta__sub">
                                    <span id="pageLabel">Page 1</span>
                                    <span>•</span>
                                    <span id="pageCount">/ ?</span>
                                    <span id="docStatus" class="bi bi-circle-fill" style="font-size: 4px; vertical-align: middle; margin-left: 6px;"></span>
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
                                <button class="btn" id="rotate"title="Rotate"><i class="bi bi-arrow-clockwise"></i></button>
                                <button class="btn" id="fit"title="Fit width"><i class="bi bi-arrows-angle-expand"></i></button>
                                <button class="btn" id="zoomOut"title="Zoom out"><i class="bi bi-dash"></i></button>
                                <button class="btn" id="zoomIn"title="Zoom in"><i class="bi bi-plus"></i></button>
                                <button class="btn" id="fs"title="Full screen"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>

                            <input id="pageNum" class="input--num" type="number" value="1" min="1"/>

                            @if($allowDownload)
                                <form id="dlForm" method="post" action="{{ $downloadRoute }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="s" value="{{ $slug }}">
                                    <button type="submit" class="cta" title="Download">
                                        <i class="bi bi-download"></i> <span>Download</span>
                                    </button>
                                </form>
                            @else
                                <button class="cta" disabled title="Download disabled">
                                    <i class="bi bi-download"></i> <span>Download</span>
                                </button>
                            @endif

                            <div class="seg">
                                <button class="btn" id="themeDark"title="Dark"><i class="bi bi-moon"></i></button>
                                <button class="btn" id="themeLight" title="Light"><i class="bi bi-sun"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar overlay for mobile --}}
                <div id="sideOverlay" class="side-overlay"></div>

                {{-- BODY --}}
                <div class="shell">
                    {{-- LEFT: Thumbnails --}}
                    <aside id="side" class="side">
                        <div class="side__head">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <strong>Page Thumbnails</strong>
                            <button id="closeSide" class="btn" style="margin-left: auto;"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div id="thumbList" class="side__list"></div>

                        <div class="doc-info">
                            <div class="doc-info__item">
                                <span class="doc-info__label">File name:</span>
                                <span id="docFileName">{{ $doc->original_name }}</span>
                            </div>
                            <div class="doc-info__item">
                                <span class="doc-info__label">Pages:</span>
                                <span id="docPageCount">Loading...</span>
                            </div>
                            <div class="doc-info__item">
                                <span class="doc-info__label">File size:</span>
                                <span id="docFileSize">Loading...</span>
                            </div>
                        </div>
                    </aside>

                    {{-- RIGHT: Main canvas --}}
                    <main class="main">
                        <div class="loader" id="loader">
                            <div class="loader__spinner"></div>
                        </div>

                        <div id="canvasWrap" class="canvas-wrap">
                            <div id="pdfContainer"></div>
                        </div>

                        <div class="bottombar">
                            <div class="badge"><i class="bi lightbulb"></i> Tips: ←/→ page • +/- zoom • F full-screen • / search</div>
                            <div class="progress" aria-label="Progress">
                                <div id="prog" class="progress__bar"></div>
                            </div>
                            <div class="badge" id="zoomLevel">Zoom: 100%</div>
                        </div>
                    </main>
                </div>

                {{-- Toast notification --}}
                <div class="toast" id="toast">
                    <i class="toast__icon bi"></i>
                    <div class="toast__message"></div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- pdf.js core (no viewer.html) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

<script>
    (function(){
        const csrf= document.querySelector('meta[name="csrf-token"]').content;
        const postUrl= @json($streamRoute);
        const slug= @json($slug);
        const nonce= @json($nonce);

        // Elements
        const wrap = document.getElementById('canvasWrap');
        const container = document.getElementById('pdfContainer');
        const loader = document.getElementById('loader');
        const toast = document.getElementById('toast');
        const docStatus = document.getElementById('docStatus');

        const pageNumEl = document.getElementById('pageNum');
        const pageCountEl = document.getElementById('pageCount');
        const pageLabel = document.getElementById('pageLabel');
        const zoomLevel = document.getElementById('zoomLevel');

        const zoomInBtn = document.getElementById('zoomIn');
        const zoomOutBtn = document.getElementById('zoomOut');
        const fitBtn = document.getElementById('fit');
        const rotateBtn = document.getElementById('rotate');
        const fsBtn = document.getElementById('fs');

        const themeDarkBtn= document.getElementById('themeDark');
        const themeLightBtn = document.getElementById('themeLight');
        const prog = document.getElementById('prog');

        const side = document.getElementById('side');
        const sideOverlay = document.getElementById('sideOverlay');
        const sideToggle = document.getElementById('toggleSide');
        const closeSide = document.getElementById('closeSide');

        // Search elements
        const qInput = document.getElementById('q');
        const qPrev= document.getElementById('qPrev');
        const qNext= document.getElementById('qNext');
        const qCount = document.getElementById('qCount');

        // State
        let pdfDoc = null, pageNum = 1, scale = 1.15, rotation = 0;
        let pageData = [];
        let searchHits = [];
        let hitIndex = -1;
        let fileSize = 0;
        let currentQuery = '';

        const clamp = (n,min,max)=>Math.max(min,Math.min(max,n));
        const dpr = () => (window.devicePixelRatio || 1);

        // Show/hide loader
        function setLoading(loading) {
            loader.style.display = loading ? 'block' : 'none';
            docStatus.style.color = loading ? '#f59e0b' : '#16a34a';
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const icon = toast.querySelector('.bi');
            const messageEl = toast.querySelector('.toast__message');

            toast.className = 'toast';
            icon.className = `toast__icon bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'}`;
            messageEl.textContent = message;

            toast.classList.add('toast--visible', `toast--${type}`);

            setTimeout(() => {
                toast.classList.remove('toast--visible');
            }, 3000);
        }

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
            zoomLevel.textContent = `Zoom: ${Math.round(scale * 100)}%`;
            setProgress();
            document.querySelectorAll('.thumb').forEach(el=>{
                const p = Number(el.dataset.page);
                el.classList.toggle('thumb--active', p === pageNum);
            });
        }

        function fitWidth(page){
            const view = page.getViewport({ scale: 1, rotation });
            const w = wrap.clientWidth - 40;
            scale = clamp(w / view.width, 0.4, 4);
        }

        async function renderAllPages(){
            setLoading(true);
            container.innerHTML = '';
            for(let num=1; num<=pdfDoc.numPages; num++){
                try{
                    const page = await pdfDoc.getPage(num);
                    if(renderAllPages._fitOnce && num===1){ fitWidth(page); renderAllPages._fitOnce = false; }
                    const viewport = page.getViewport({ scale, rotation });
                    const ratio = dpr();
                    const pageWrapper = document.createElement('div');
                    pageWrapper.style.position = 'relative';
                    pageWrapper.dataset.pageNumber = num;

                    const canvas = document.createElement('canvas');
                    canvas.width= Math.floor(viewport.width * ratio);
                    canvas.height = Math.floor(viewport.height * ratio);
                    canvas.style.width= Math.floor(viewport.width) + 'px';
                    canvas.style.height = Math.floor(viewport.height) + 'px';
                    const ctx = canvas.getContext('2d', { alpha:false, desynchronized:true });
                    ctx.setTransform(ratio,0,0,ratio,0,0);
                    ctx.fillStyle = '#0a0f1e';
                    ctx.fillRect(0,0,canvas.width,canvas.height);
                    await page.render({ canvasContext: ctx, viewport }).promise;
                    pageWrapper.appendChild(canvas);
                    container.appendChild(pageWrapper);

                    const textContent = await page.getTextContent();
                    const textItems = textContent.items.map(item => item.str).join('');
                    pageData[num] = { text: textItems, textContentItems: textContent.items };
                }catch(error){
                    console.error('Error rendering page:', error);
                }
            }
            if(currentQuery){
                highlightSearchResults(currentQuery);
            }
            setLoading(false);
            updateUI();
        }

        function scrollToPage(p){
            const pageWrapper = container.querySelector(`div[data-page-number="${p}"]`);
            if(pageWrapper){
                wrap.scrollTo({ top: pageWrapper.offsetTop - 10, behavior: 'smooth' });
            }
        }

        function updateCurrentPage(){
            const center = wrap.scrollTop + wrap.clientHeight / 2;
            const pageWrappers = container.querySelectorAll('div[data-page-number]');
            let current = pageNum;
            pageWrappers.forEach((c, i)=>{
                const top = c.offsetTop;
                const bottom = top + c.offsetHeight;
                if(center >= top && center < bottom){ current = i+1; }
            });
            if(current !== pageNum){
                pageNum = current;
                updateUI();
            }
        }
        wrap.addEventListener('scroll', updateCurrentPage);

        // Thumbnails
        async function buildThumbnails(){
            const list = document.getElementById('thumbList');
            list.innerHTML = '';

            for (let p=1; p<=pdfDoc.numPages; p++){
                const item = document.createElement('div');
                item.className='thumb';
                item.dataset.page=String(p);

                const c = document.createElement('canvas');
                c.className='thumb__canvas';
                c.width=180;
                c.height=240;

                const meta = document.createElement('div');
                meta.className='thumb__meta';
                meta.innerHTML = `<span>Page ${p}</span><span class="bi bi-chevron-right" style="opacity: 0.5"></span>`;

                item.appendChild(c);
                item.appendChild(meta);
                list.appendChild(item);

                // render tiny preview
                try {
                    const page = await pdfDoc.getPage(p);
                    const vp = page.getViewport({ scale: 0.25 });
                    const r = Math.min(180 / vp.width, 240 / vp.height);
                    const v2 = page.getViewport({ scale: 0.25 * r });
                    const tctx = c.getContext('2d');
                    c.width = Math.floor(v2.width);
                    c.height = Math.floor(v2.height);

                    // Clear with background
                    tctx.fillStyle = '#0a0f1e';
                    tctx.fillRect(0, 0, c.width, c.height);

                    await page.render({ canvasContext: tctx, viewport: v2 }).promise;
                } catch (error) {
                    console.error('Error generating thumbnail:', error);
                }

                item.addEventListener('click', ()=>{
                    pageNum = p;
                    scrollToPage(pageNum);
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            }
        }

        // Search
        async function runSearch(q){
            searchHits = [];
            hitIndex = -1;
            currentQuery = q;
            qCount.textContent = '';

            if (!q || q.trim().length < 2) {
                clearHighlights();
                showToast('Enter at least 2 characters to search', 'error');
                return;
            }
            setLoading(true);

            try {
                for(let p=1; p<=pdfDoc.numPages; p++){
                    if(!pageData[p]) {
                        // Ensure text content is loaded for the page
                        const page = await pdfDoc.getPage(p);
                        const textContent = await page.getTextContent();
                        const textItems = textContent.items.map(item => item.str).join('');
                        pageData[p] = { text: textItems, textContentItems: textContent.items };
                    }

                    const pageText = pageData[p].text.toLowerCase();
                    const needle = q.toLowerCase();
                    let idx = pageText.indexOf(needle);
                    while(idx !== -1){
                        searchHits.push({page:p, index:idx});
                        idx = pageText.indexOf(needle, idx+needle.length);
                    }
                }

                if (searchHits.length){
                    hitIndex = 0;
                    qCount.textContent = `${hitIndex+1}/${searchHits.length}`;
                    showToast(`Found ${searchHits.length} matches`, 'success');
                    highlightSearchResults(q);
                    pageNum = searchHits[0].page;
                    scrollToPage(pageNum);
                } else {
                    qCount.textContent = '0/0';
                    showToast('No matches found', 'error');
                    clearHighlights();
                }
            } catch (error) {
                console.error('Search error:', error);
                showToast('Search error', 'error');
            } finally {
                setLoading(false);
            }
        }

        function navSearch(delta){
            if (!searchHits.length) return;
            hitIndex = (hitIndex + delta + searchHits.length) % searchHits.length;
            pageNum = searchHits[hitIndex].page;
            highlightSearchResults(currentQuery);
            scrollToPage(pageNum);
            qCount.textContent = `${hitIndex+1}/${searchHits.length}`;
        }

        async function highlightSearchResults(query) {
            clearHighlights();
            if (!searchHits.length || !query) return;

            // Group hits by page
            const hitsByPage = searchHits.reduce((acc, hit) => {
                if (!acc[hit.page]) acc[hit.page] = [];
                acc[hit.page].push(hit);
                return acc;
            }, {});

            for (const pageNumber in hitsByPage) {
                const pageHits = hitsByPage[pageNumber];
                const pageWrapper = container.querySelector(`div[data-page-number="${pageNumber}"]`);
                if (!pageWrapper) continue;

                const page = await pdfDoc.getPage(Number(pageNumber));
                const viewport = page.getViewport({ scale: scale, rotation: rotation });
                const textContent = await page.getTextContent();

                const textLayer = document.createElement('div');
                textLayer.className = 'text-layer';
                pageWrapper.appendChild(textLayer);
                
                let charIndex = 0;
                const queryLen = query.length;

                for (let i = 0; i < textContent.items.length; i++) {
                    const item = textContent.items[i];
                    const itemStr = item.str;
                    const itemStrLen = itemStr.length;
                    
                    // Find all matches within the current text item
                    let matchStart = itemStr.toLowerCase().indexOf(query.toLowerCase());
                    while (matchStart !== -1) {
                        // Check if this visual match corresponds to one of our pre-calculated search hits
                        const fullTextIndex = charIndex + matchStart;
                        const hit = pageHits.find(h => h.index === fullTextIndex);

                        if (hit) {
                            const highlightEl = document.createElement('div');
                            highlightEl.className = 'highlight';

                            // Use the text item's bounding box for positioning
                            const bBox = viewport.convertToViewportRectangle([
                                item.transform[4],
                                item.transform[5],
                                item.transform[4] + item.width,
                                item.transform[5] + item.height
                            ]);

                            highlightEl.style.left = bBox[0] + 'px';
                            highlightEl.style.top = bBox[1] + 'px';
                            highlightEl.style.width = (bBox[2] - bBox[0]) + 'px';
                            highlightEl.style.height = (bBox[3] - bBox[1]) + 'px';

                            // Apply the 'active' class if this is the currently selected hit
                            if (searchHits[hitIndex] && searchHits[hitIndex].page === hit.page && searchHits[hitIndex].index === hit.index) {
                                highlightEl.classList.add('highlight--active');
                            }
                            textLayer.appendChild(highlightEl);
                        }
                        matchStart = itemStr.toLowerCase().indexOf(query.toLowerCase(), matchStart + queryLen);
                    }
                    charIndex += itemStrLen;
                }
            }
        }
        
        function clearHighlights() {
            document.querySelectorAll('.text-layer').forEach(layer => layer.remove());
        }
        // Sidebar functions
        function toggleSidebar() {
            side.classList.toggle('side--open');
            sideOverlay.classList.toggle('side-overlay--visible');
        }

        function closeSidebar() {
            side.classList.remove('side--open');
            sideOverlay.classList.remove('side-overlay--visible');
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Fetch PDF bytes via POST (no URL exposure)
        async function loadBytes(){
            const res = await fetch(postUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: (()=>{ const fd = new FormData(); fd.append('s', slug); fd.append('nonce', nonce); return fd; })()
            });

            if (!res.ok) throw new Error('Failed to load PDF');

            // Get file size from headers
            const contentLength = res.headers.get('Content-Length');
            if (contentLength) {
                fileSize = parseInt(contentLength, 10);
                document.getElementById('docFileSize').textContent = formatFileSize(fileSize);
            }

            return await res.arrayBuffer();
        }

        // Controls
        zoomInBtn.onclick = ()=>{ scale = clamp(scale + 0.15, 0.4, 6); renderAllPages(); scrollToPage(pageNum); };
        zoomOutBtn.onclick= ()=>{ scale = clamp(scale - 0.15, 0.3, 6); renderAllPages(); scrollToPage(pageNum); };
        fitBtn.onclick= ()=>{ renderAllPages._fitOnce = true; renderAllPages(); scrollToPage(pageNum); };
        rotateBtn.onclick = ()=>{ rotation = (rotation + 90) % 360; renderAllPages(); scrollToPage(pageNum); };
        fsBtn.onclick= ()=>{
            const el = document.documentElement;
            if(!document.fullscreenElement){
                el.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        };

        pageNumEl.onchange= (e)=>{
            const v = clamp(parseInt(e.target.value||'1',10),1,pdfDoc.numPages);
            pageNum=v;
            scrollToPage(pageNum);
        };

        // Theme buttons
        themeDarkBtn.onclick= ()=>{
            document.body.classList.add('theme--dark');
            document.body.classList.remove('theme--light');
            localStorage.setItem('pdfViewerTheme', 'dark');
        };

        themeLightBtn.onclick = ()=>{
            document.body.classList.add('theme--light');
            document.body.classList.remove('theme--dark');
            localStorage.setItem('pdfViewerTheme', 'light');
        };

        // Sidebar toggle
        sideToggle?.addEventListener('click', toggleSidebar);
        closeSide?.addEventListener('click', closeSidebar);
        sideOverlay?.addEventListener('click', closeSidebar);

        // Keyboard
        document.addEventListener('keydown', (e)=>{
            const tag = (document.activeElement?.tagName||'').toUpperCase();
            const typing = (tag === 'INPUT' || tag === 'TEXTAREA');

            if (!typing) {
                switch(e.key) {
                    case 'ArrowRight':
                    case 'PageDown':
                        if(pageNum < pdfDoc.numPages){ pageNum++; scrollToPage(pageNum); }
                        break;
                    case 'ArrowLeft':
                    case 'PageUp':
                        if(pageNum > 1){ pageNum--; scrollToPage(pageNum); }
                        break;
                    case '+':
                        zoomInBtn.click();
                        break;
                    case '-':
                        zoomOutBtn.click();
                        break;
                    case 'f':
                        fsBtn.click();
                        break;
                    case 'Escape':
                        closeSidebar();
                        break;
                }
            }

            if (e.key === '/') {
                e.preventDefault();
                qInput.focus();
            }
        });

        // Search events
        let searchTimer=null;
        qInput.addEventListener('input', ()=>{
            clearTimeout(searchTimer);
            searchTimer=setTimeout(()=>runSearch(qInput.value), 350);
        });

        qPrev.onclick = ()=> navSearch(-1);
        qNext.onclick = ()=> navSearch(1);

        // Resize → refit (debounced)
        let rzTimer=null;
        window.addEventListener('resize', ()=>{
            clearTimeout(rzTimer);
            rzTimer=setTimeout(()=>{
                renderAllPages._fitOnce = true;
                renderAllPages();
                scrollToPage(pageNum);
            }, 200);
        });

        // Init
        (async ()=>{
            try{
                setLoading(true);
                docStatus.style.color = '#f59e0b';

                const buf = await loadBytes();
                pdfDoc = await pdfjsLib.getDocument({ data: buf }).promise;

                // Update document info
                document.getElementById('docPageCount').textContent = pdfDoc.numPages;

                await buildThumbnails();
                renderAllPages._fitOnce = true;
                await renderAllPages();
                scrollToPage(pageNum);
                updateUI();

                // Apply saved theme
                const savedTheme = localStorage.getItem('pdfViewerTheme') || 'dark';
                if (savedTheme === 'light') {
                    themeLightBtn.click();
                } else {
                    themeDarkBtn.click();
                }

                setLoading(false);
                showToast('Document loaded successfully', 'success');
            }catch(err){
                console.error('PDF loading error:', err);
                showToast('Unable to open document: ' + err.message, 'error');
                docStatus.style.color = '#ef4444';
            }
        })();
    })();
</script>
</body>
</html>