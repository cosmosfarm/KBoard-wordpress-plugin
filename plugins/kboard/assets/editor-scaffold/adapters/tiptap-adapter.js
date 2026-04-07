(function (global) {
  'use strict';

  var registry = global.KBoardEditorAdapters;
  if (!registry) {
    throw new Error('[KBoardTipTapAdapter] Load kboard-editor-registry.js first.');
  }

  /* ── CSS ──────────────────────────────────────────────── */
  var cssInjected = false;
  function injectCSS() {
    if (cssInjected) return;
    cssInjected = true;
    var s = document.createElement('style');
    s.id = 'kboard-tiptap-toolbar-css';
    s.textContent =
      /* Reset scaffold wrapper when tiptap is active */
      '.kboard-editor-root.kboard-editor-root-tiptap{padding:0!important;border:none!important;background:transparent!important;box-shadow:none!important;min-height:auto!important;overflow:visible!important}' +

      /* Wrap */
      '.kboard-tiptap-wrap{border:1px solid var(--kboard-border,#d1d5db);border-radius:var(--kboard-radius,8px);overflow:hidden;background:#fff}' +
      '.kboard-tiptap-wrap:focus-within{border-color:var(--kboard-primary,#2563eb);box-shadow:0 0 0 3px rgba(37,99,235,.1)}' +

      /* Toolbar */
      '.kboard-tiptap-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:2px;padding:6px 8px;background:#f8f9fa;border-bottom:1px solid var(--kboard-border,#d1d5db);user-select:none}' +
      '.kboard-tiptap-toolbar-group{display:flex;align-items:center;gap:1px}' +
      '.kboard-tiptap-toolbar-divider{width:1px;height:24px;background:var(--kboard-border,#d1d5db);margin:0 4px;flex-shrink:0}' +

      /* Button */
      '.kboard-tiptap-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;padding:0;border:1px solid transparent;border-radius:4px;background:transparent;color:#374151;cursor:pointer;font-family:inherit;font-size:14px;line-height:1;transition:background .12s,border-color .12s,color .12s}' +
      '.kboard-tiptap-btn:hover{background:#e5e7eb;border-color:#d1d5db}' +
      '.kboard-tiptap-btn.active{background:#dbeafe;color:#2563eb;border-color:#93c5fd}' +
      '.kboard-tiptap-btn:disabled{opacity:.35;cursor:default;pointer-events:none}' +
      '.kboard-tiptap-btn svg{width:18px;height:18px;flex-shrink:0}' +

      /* Select */
      '.kboard-tiptap-select{height:32px;padding:0 22px 0 8px;border:1px solid transparent;border-radius:4px;background:transparent;color:#374151;cursor:pointer;font-family:inherit;font-size:13px;' +
        "appearance:none;-webkit-appearance:none;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 4px center;background-size:14px}" +
      '.kboard-tiptap-select:hover{background-color:#e5e7eb;border-color:#d1d5db}' +
      '.kboard-tiptap-select:focus{outline:none;border-color:#93c5fd}' +

      /* Editor area */
      '.kboard-tiptap-wrap .ProseMirror{min-height:300px;padding:16px 20px;outline:none;font-size:15px;line-height:1.75;color:#1f2937}' +
      '.kboard-tiptap-wrap .ProseMirror>*+*{margin-top:.75em}' +
      '.kboard-tiptap-wrap .ProseMirror p.is-editor-empty:first-child::before{content:attr(data-placeholder);color:#9ca3af;pointer-events:none;float:left;height:0}' +
      '.kboard-tiptap-wrap .ProseMirror h1{font-size:2em;font-weight:700;line-height:1.3}' +
      '.kboard-tiptap-wrap .ProseMirror h2{font-size:1.5em;font-weight:600;line-height:1.35}' +
      '.kboard-tiptap-wrap .ProseMirror h3{font-size:1.25em;font-weight:600;line-height:1.4}' +
      '.kboard-tiptap-wrap .ProseMirror ul,.kboard-tiptap-wrap .ProseMirror ol{padding-left:1.5em}' +
      '.kboard-tiptap-wrap .ProseMirror blockquote{border-left:3px solid var(--kboard-border,#d1d5db);padding-left:1em;color:#6b7280;font-style:italic}' +
      '.kboard-tiptap-wrap .ProseMirror pre{background:#1e293b;color:#e2e8f0;padding:12px 16px;border-radius:6px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:14px;overflow-x:auto}' +
      '.kboard-tiptap-wrap .ProseMirror code{background:#f1f5f9;padding:2px 5px;border-radius:3px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.9em}' +
      '.kboard-tiptap-wrap .ProseMirror pre code{background:none;padding:0;border-radius:0;font-size:inherit}' +
      '.kboard-tiptap-wrap .ProseMirror img{max-width:100%;height:auto;border-radius:4px}' +
      '.kboard-tiptap-wrap .ProseMirror hr{border:none;border-top:2px solid #e5e7eb;margin:1.5em 0}' +
      '.kboard-tiptap-wrap .ProseMirror a{color:#2563eb;text-decoration:underline}' +
      '.kboard-tiptap-wrap .ProseMirror mark{border-radius:2px;padding:1px 2px;box-decoration-break:clone;-webkit-box-decoration-break:clone}' +

      /* Table */
      '.kboard-tiptap-wrap .ProseMirror table{border-collapse:collapse;width:100%;margin:1em 0;table-layout:fixed;overflow:hidden}' +
      '.kboard-tiptap-wrap .ProseMirror th,.kboard-tiptap-wrap .ProseMirror td{border:1px solid #d1d5db;padding:8px 12px;text-align:left;vertical-align:top;position:relative}' +
      '.kboard-tiptap-wrap .ProseMirror th{background:#f3f4f6;font-weight:600}' +
      '.kboard-tiptap-wrap .ProseMirror .selectedCell::after{content:"";position:absolute;inset:0;background:rgba(37,99,235,.08);pointer-events:none}' +
      '.kboard-tiptap-wrap .ProseMirror .column-resize-handle{position:absolute;right:-2px;top:0;bottom:0;width:4px;background:var(--kboard-primary,#2563eb);pointer-events:none}' +
      '.kboard-tiptap-wrap .ProseMirror.resize-cursor{cursor:col-resize}' +

      /* Task list */
      '.kboard-tiptap-wrap .ProseMirror ul[data-type="taskList"]{list-style:none;padding-left:0}' +
      '.kboard-tiptap-wrap .ProseMirror ul[data-type="taskList"] li{display:flex;align-items:flex-start;gap:8px}' +
      '.kboard-tiptap-wrap .ProseMirror ul[data-type="taskList"] li>label{flex-shrink:0;margin-top:3px}' +
      '.kboard-tiptap-wrap .ProseMirror ul[data-type="taskList"] li>div{flex:1 1 auto}' +

      /* Dropdowns */
      '.kboard-tiptap-dropdown{position:absolute;z-index:1000;background:#fff;border:1px solid #d1d5db;border-radius:8px;padding:6px;box-shadow:0 4px 16px rgba(0,0,0,.12);display:none;left:0;top:100%;margin-top:4px}' +
      '.kboard-tiptap-dropdown.open{display:block}' +
      '.kboard-tiptap-color-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:3px}' +
      '.kboard-tiptap-color-swatch{width:24px;height:24px;border-radius:4px;border:2px solid #e5e7eb;cursor:pointer;transition:transform .1s,border-color .1s;padding:0;background:none}' +
      '.kboard-tiptap-color-swatch:hover{transform:scale(1.15);border-color:#9ca3af}' +
      '.kboard-tiptap-color-reset{width:100%;margin-top:6px;padding:5px;background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;font-size:12px;text-align:center;color:#374151}' +
      '.kboard-tiptap-color-reset:hover{background:#e5e7eb}' +
      '.kboard-tiptap-table-menu button{display:block;width:100%;text-align:left;padding:6px 12px;border:none;background:transparent;cursor:pointer;font-size:13px;border-radius:4px;color:#374151;white-space:nowrap}' +
      '.kboard-tiptap-table-menu button:hover{background:#f3f4f6}' +
      '.kboard-tiptap-table-sep{border-top:1px solid #e5e7eb;margin:4px 0}';

    document.head.appendChild(s);
  }

  /* ── SVG helper ──────────────────────────────────────── */
  var SA = ' xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
  function ic(d) { return '<svg' + SA + '>' + d + '</svg>'; }

  /* ── Color palettes ──────────────────────────────────── */
  var TEXT_COLORS = [
    '#000000','#434343','#666666','#999999','#b7b7b7','#cccccc','#d9d9d9','#ffffff',
    '#980000','#ff0000','#ff9900','#ffff00','#00ff00','#00ffff','#4a86e8','#0000ff',
    '#9900ff','#ff00ff','#e6b8af','#f4cccc','#fce5cd','#fff2cc','#d9ead3','#d0e0e3',
    '#c9daf8','#cfe2f3','#d9d2e9','#ead1dc','#dd7e6b','#ea9999','#f9cb9c','#ffe599',
    '#b6d7a8','#a2c4c9','#a4c2f4','#9fc5e8','#b4a7d6','#d5a6bd','#cc4125','#e06666',
    '#f6b26b','#ffd966','#93c47d','#76a5af','#6d9eeb','#6fa8dc','#8e7cc3','#c27ba0'
  ];
  var HL_COLORS = [
    '#fef08a','#bbf7d0','#a5f3fc','#c4b5fd','#fecaca',
    '#fed7aa','#fbcfe8','#e5e7eb','#bfdbfe','#d9f99d'
  ];

  /* ── Dropdown state ──────────────────────────────────── */
  var activeDrop = null;
  function toggleDrop(drop) {
    if (activeDrop && activeDrop !== drop) activeDrop.classList.remove('open');
    drop.classList.toggle('open');
    activeDrop = drop.classList.contains('open') ? drop : null;
  }
  document.addEventListener('click', function (e) {
    if (activeDrop && !activeDrop.parentElement.contains(e.target)) {
      activeDrop.classList.remove('open');
      activeDrop = null;
    }
  });

  /* ── Color dropdown builder ──────────────────────────── */
  function colorDropdown(colors, onPick, resetLabel) {
    var drop = document.createElement('div');
    drop.className = 'kboard-tiptap-dropdown';
    var grid = document.createElement('div');
    grid.className = 'kboard-tiptap-color-grid';
    for (var i = 0; i < colors.length; i++) {
      var sw = document.createElement('button');
      sw.type = 'button';
      sw.className = 'kboard-tiptap-color-swatch';
      sw.style.backgroundColor = colors[i];
      sw.title = colors[i];
      sw.addEventListener('click', (function (c) {
        return function (e) { e.preventDefault(); e.stopPropagation(); onPick(c); drop.classList.remove('open'); activeDrop = null; };
      })(colors[i]));
      grid.appendChild(sw);
    }
    drop.appendChild(grid);
    var rst = document.createElement('button');
    rst.type = 'button';
    rst.className = 'kboard-tiptap-color-reset';
    rst.textContent = resetLabel || '초기화';
    rst.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); onPick(null); drop.classList.remove('open'); activeDrop = null; });
    drop.appendChild(rst);
    return drop;
  }

  /* ── Table dropdown builder ──────────────────────────── */
  function tableDropdown(editor) {
    var drop = document.createElement('div');
    drop.className = 'kboard-tiptap-dropdown kboard-tiptap-table-menu';
    var items = [
      ['표 삽입 (3×3)', function () { editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(); }],
      null,
      ['위에 행 추가', function () { editor.chain().focus().addRowBefore().run(); }],
      ['아래에 행 추가', function () { editor.chain().focus().addRowAfter().run(); }],
      ['행 삭제', function () { editor.chain().focus().deleteRow().run(); }],
      null,
      ['왼쪽에 열 추가', function () { editor.chain().focus().addColumnBefore().run(); }],
      ['오른쪽에 열 추가', function () { editor.chain().focus().addColumnAfter().run(); }],
      ['열 삭제', function () { editor.chain().focus().deleteColumn().run(); }],
      null,
      ['셀 병합', function () { editor.chain().focus().mergeCells().run(); }],
      ['셀 분할', function () { editor.chain().focus().splitCell().run(); }],
      null,
      ['표 삭제', function () { editor.chain().focus().deleteTable().run(); }]
    ];
    for (var i = 0; i < items.length; i++) {
      if (!items[i]) {
        var sep = document.createElement('div');
        sep.className = 'kboard-tiptap-table-sep';
        drop.appendChild(sep);
        continue;
      }
      var b = document.createElement('button');
      b.type = 'button';
      b.textContent = items[i][0];
      b.addEventListener('click', (function (fn) {
        return function (e) { e.preventDefault(); e.stopPropagation(); fn(); drop.classList.remove('open'); activeDrop = null; };
      })(items[i][1]));
      drop.appendChild(b);
    }
    return drop;
  }

  /* ── Link handler ────────────────────────────────────── */
  function handleLink(editor) {
    if (editor.isActive('link')) {
      editor.chain().focus().unsetLink().run();
      return;
    }
    var url = prompt('URL을 입력하세요:', 'https://');
    if (url && url !== 'https://') {
      editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }
  }

  /* ── Image handler ───────────────────────────────────── */
  function handleImage() {
    if (typeof global.kboard_editor_open_media === 'function') {
      global.kboard_editor_open_media();
    } else {
      var url = prompt('이미지 URL을 입력하세요:');
      if (url && global.kboard_editor_runtime && global.kboard_editor_runtime.adapter) {
        global.kboard_editor_runtime.adapter.insertMedia(url);
      }
    }
  }

  /* ── Toolbar builder ─────────────────────────────────── */
  function buildToolbar(editor, ext) {
    var toolbar = document.createElement('div');
    toolbar.className = 'kboard-tiptap-toolbar';
    var headingSelect = null;

    function btn(html, title, onClick, cls) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'kboard-tiptap-btn' + (cls ? ' ' + cls : '');
      b.title = title;
      b.innerHTML = html;
      b.addEventListener('click', function (e) { e.preventDefault(); onClick(b); });
      return b;
    }
    function divider() {
      var d = document.createElement('div');
      d.className = 'kboard-tiptap-toolbar-divider';
      return d;
    }
    function group(items) {
      var g = document.createElement('div');
      g.className = 'kboard-tiptap-toolbar-group';
      for (var i = 0; i < items.length; i++) {
        if (items[i]) g.appendChild(items[i]);
      }
      return g;
    }
    function wrap(child) {
      var w = document.createElement('div');
      w.style.position = 'relative';
      w.style.display = 'inline-flex';
      return w;
    }

    // ─── History ───
    toolbar.appendChild(group([
      btn(ic('<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>'), '실행 취소 (Ctrl+Z)', function () { editor.chain().focus().undo().run(); }),
      btn(ic('<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.13-9.36L23 10"/>'), '다시 실행 (Ctrl+Y)', function () { editor.chain().focus().redo().run(); })
    ]));
    toolbar.appendChild(divider());

    // ─── Heading ───
    headingSelect = document.createElement('select');
    headingSelect.className = 'kboard-tiptap-select';
    headingSelect.title = '문단 스타일';
    var hOpts = [['paragraph', '본문'], ['1', '제목 1'], ['2', '제목 2'], ['3', '제목 3']];
    for (var hi = 0; hi < hOpts.length; hi++) {
      var o = document.createElement('option');
      o.value = hOpts[hi][0];
      o.textContent = hOpts[hi][1];
      headingSelect.appendChild(o);
    }
    headingSelect.addEventListener('change', function () {
      var v = headingSelect.value;
      if (v === 'paragraph') editor.chain().focus().setParagraph().run();
      else editor.chain().focus().toggleHeading({ level: parseInt(v, 10) }).run();
    });
    toolbar.appendChild(group([headingSelect]));
    toolbar.appendChild(divider());

    // ─── Font size (via heading) ───

    // ─── Text format ───
    toolbar.appendChild(group([
      btn('<b style="font-size:15px">B</b>', '굵게 (Ctrl+B)', function () { editor.chain().focus().toggleBold().run(); }, 'tb-bold'),
      btn('<i style="font-size:15px;font-family:Georgia,serif">I</i>', '기울임 (Ctrl+I)', function () { editor.chain().focus().toggleItalic().run(); }, 'tb-italic'),
      btn('<span style="font-size:15px;text-decoration:underline">U</span>', '밑줄 (Ctrl+U)', function () { editor.chain().focus().toggleUnderline().run(); }, 'tb-underline'),
      btn('<span style="font-size:15px;text-decoration:line-through">S</span>', '취소선', function () { editor.chain().focus().toggleStrike().run(); }, 'tb-strike'),
      btn(ic('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'), '인라인 코드', function () { editor.chain().focus().toggleCode().run(); }, 'tb-code')
    ]));
    toolbar.appendChild(divider());

    // ─── Color ───
    if (ext.Color && ext.TextStyle) {
      var cW = wrap();
      var cBtn = btn('<span class="kb-color-ind" style="font-size:15px;font-weight:700;display:inline-block;border-bottom:3px solid #000;line-height:1.1">A</span>', '글자 색', function () { toggleDrop(cDrop); }, 'tb-textColor');
      var cDrop = colorDropdown(TEXT_COLORS, function (c) {
        if (c) {
          editor.chain().focus().setColor(c).run();
          cBtn.querySelector('.kb-color-ind').style.borderBottomColor = c;
        } else {
          editor.chain().focus().unsetColor().run();
          cBtn.querySelector('.kb-color-ind').style.borderBottomColor = '#000';
        }
      }, '색상 초기화');
      cW.appendChild(cBtn);
      cW.appendChild(cDrop);

      var gItems = [cW];

      if (ext.Highlight) {
        var hW = wrap();
        var hBtn = btn(ic('<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>'), '형광펜', function () { toggleDrop(hDrop); }, 'tb-highlight');
        var hDrop = colorDropdown(HL_COLORS, function (c) {
          if (c) editor.chain().focus().toggleHighlight({ color: c }).run();
          else editor.chain().focus().unsetHighlight().run();
        }, '형광펜 제거');
        hW.appendChild(hBtn);
        hW.appendChild(hDrop);
        gItems.push(hW);
      }
      toolbar.appendChild(group(gItems));
      toolbar.appendChild(divider());
    }

    // ─── Alignment ───
    if (ext.TextAlign) {
      toolbar.appendChild(group([
        btn(ic('<line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/>'), '왼쪽 정렬', function () { editor.chain().focus().setTextAlign('left').run(); }, 'tb-align-left'),
        btn(ic('<line x1="18" y1="10" x2="6" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="18" y1="18" x2="6" y2="18"/>'), '가운데 정렬', function () { editor.chain().focus().setTextAlign('center').run(); }, 'tb-align-center'),
        btn(ic('<line x1="21" y1="10" x2="7" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="7" y2="18"/>'), '오른쪽 정렬', function () { editor.chain().focus().setTextAlign('right').run(); }, 'tb-align-right'),
        btn(ic('<line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/>'), '양쪽 정렬', function () { editor.chain().focus().setTextAlign('justify').run(); }, 'tb-align-justify')
      ]));
      toolbar.appendChild(divider());
    }

    // ─── Lists ───
    var listItems = [
      btn(ic('<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="3" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="3" cy="18" r="1.5" fill="currentColor" stroke="none"/>'), '글머리 기호', function () { editor.chain().focus().toggleBulletList().run(); }, 'tb-bulletList'),
      btn(ic('<line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>'), '번호 매기기', function () { editor.chain().focus().toggleOrderedList().run(); }, 'tb-orderedList')
    ];
    if (ext.TaskList && ext.TaskItem) {
      listItems.push(
        btn(ic('<rect x="3" y="5" width="6" height="6" rx="1"/><path d="M5.5 8l1 1 2-2"/><line x1="13" y1="8" x2="21" y2="8"/><rect x="3" y="14" width="6" height="6" rx="1"/><line x1="13" y1="17" x2="21" y2="17"/>'), '체크리스트', function () { editor.chain().focus().toggleTaskList().run(); }, 'tb-taskList')
      );
    }
    toolbar.appendChild(group(listItems));
    toolbar.appendChild(divider());

    // ─── Block ───
    toolbar.appendChild(group([
      btn(ic('<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 5v3z"/>'), '인용문', function () { editor.chain().focus().toggleBlockquote().run(); }, 'tb-blockquote'),
      btn(ic('<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 10l-2 2 2 2"/><path d="M14 10l2 2-2 2"/>'), '코드 블록', function () { editor.chain().focus().toggleCodeBlock().run(); }, 'tb-codeBlock'),
      btn(ic('<line x1="2" y1="12" x2="22" y2="12"/>'), '구분선', function () { editor.chain().focus().setHorizontalRule().run(); })
    ]));
    toolbar.appendChild(divider());

    // ─── Insert ───
    var insertItems = [
      btn(ic('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'), '링크 (Ctrl+K)', function () { handleLink(editor); }, 'tb-link'),
      btn(ic('<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'), '이미지', function () { handleImage(); })
    ];
    if (ext.Table) {
      var tW = wrap();
      var tBtn = btn(ic('<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/>'), '표', function () { toggleDrop(tDrop); });
      var tDrop = tableDropdown(editor);
      tW.appendChild(tBtn);
      tW.appendChild(tDrop);
      insertItems.push(tW);
    }
    toolbar.appendChild(group(insertItems));
    toolbar.appendChild(divider());

    // ─── Sub / Super ───
    var ssItems = [];
    if (ext.Subscript) {
      ssItems.push(btn('<span style="font-size:14px">X<sub style="font-size:9px">2</sub></span>', '아래 첨자', function () { editor.chain().focus().toggleSubscript().run(); }, 'tb-subscript'));
    }
    if (ext.Superscript) {
      ssItems.push(btn('<span style="font-size:14px">X<sup style="font-size:9px">2</sup></span>', '위 첨자', function () { editor.chain().focus().toggleSuperscript().run(); }, 'tb-superscript'));
    }
    if (ssItems.length) {
      toolbar.appendChild(group(ssItems));
      toolbar.appendChild(divider());
    }

    // ─── Clear ───
    toolbar.appendChild(group([
      btn('<span style="font-size:14px">T<span style="font-size:10px;vertical-align:super;color:#ef4444;font-weight:700">x</span></span>', '서식 지우기', function () { editor.chain().focus().clearNodes().unsetAllMarks().run(); })
    ]));

    // ─── Active state updater ───
    function updateStates() {
      var all = toolbar.querySelectorAll('.kboard-tiptap-btn');
      for (var i = 0; i < all.length; i++) {
        var c = all[i].className;
        var on = false;
        if (c.indexOf('tb-bold') >= 0) on = editor.isActive('bold');
        else if (c.indexOf('tb-italic') >= 0) on = editor.isActive('italic');
        else if (c.indexOf('tb-underline') >= 0) on = editor.isActive('underline');
        else if (c.indexOf('tb-strike') >= 0) on = editor.isActive('strike');
        else if (c.indexOf('tb-code') >= 0 && c.indexOf('tb-codeBlock') < 0) on = editor.isActive('code');
        else if (c.indexOf('tb-blockquote') >= 0) on = editor.isActive('blockquote');
        else if (c.indexOf('tb-codeBlock') >= 0) on = editor.isActive('codeBlock');
        else if (c.indexOf('tb-bulletList') >= 0) on = editor.isActive('bulletList');
        else if (c.indexOf('tb-orderedList') >= 0) on = editor.isActive('orderedList');
        else if (c.indexOf('tb-taskList') >= 0) on = editor.isActive('taskList');
        else if (c.indexOf('tb-link') >= 0) on = editor.isActive('link');
        else if (c.indexOf('tb-subscript') >= 0) on = editor.isActive('subscript');
        else if (c.indexOf('tb-superscript') >= 0) on = editor.isActive('superscript');
        else if (c.indexOf('tb-highlight') >= 0) on = editor.isActive('highlight');
        else if (c.indexOf('tb-align-left') >= 0) on = editor.isActive({ textAlign: 'left' });
        else if (c.indexOf('tb-align-center') >= 0) on = editor.isActive({ textAlign: 'center' });
        else if (c.indexOf('tb-align-right') >= 0) on = editor.isActive({ textAlign: 'right' });
        else if (c.indexOf('tb-align-justify') >= 0) on = editor.isActive({ textAlign: 'justify' });

        if (on) all[i].classList.add('active');
        else all[i].classList.remove('active');
      }
      // heading select
      if (headingSelect) {
        if (editor.isActive('heading', { level: 1 })) headingSelect.value = '1';
        else if (editor.isActive('heading', { level: 2 })) headingSelect.value = '2';
        else if (editor.isActive('heading', { level: 3 })) headingSelect.value = '3';
        else headingSelect.value = 'paragraph';
      }
    }

    return { element: toolbar, updateStates: updateStates };
  }

  /* ── TipTap core resolver ────────────────────────────── */
  function getTipTapCore() {
    var core = global.tiptap || global.TiptapCore || {};
    var ext = global.tiptapExtensions || global.TiptapExtensions || {};
    return { Editor: core.Editor || global.Editor, ext: ext };
  }

  /* ── Adapter registration ────────────────────────────── */
  registry.register('tiptap', function tiptapFactory(options) {
    var editor = null;
    var toolbarObj = null;
    var element = registry.utils.resolveElement(options.element || options.selector);
    var textarea = options.textarea;
    var initialHTML = typeof options.initialHTML === 'string' ? options.initialHTML : textarea.value || '';

    if (!element) {
      throw new Error('[KBoardTipTapAdapter] Missing mount element.');
    }

    return {
      init: function init() {
        var core = getTipTapCore();
        if (!core.Editor || !core.ext.StarterKit) {
          throw new Error('[KBoardTipTapAdapter] TipTap globals not found. Include TipTap scripts before this adapter.');
        }

        injectCSS();

        var ext = core.ext;
        var configured = [ext.StarterKit.configure({ heading: { levels: [1, 2, 3] } })];

        if (ext.Underline) configured.push(ext.Underline);
        if (ext.Link) configured.push(ext.Link.configure({ openOnClick: false, autolink: true, linkOnPaste: true }));
        if (ext.Image) configured.push(ext.Image.configure({ inline: false, allowBase64: true }));
        if (ext.TextAlign) configured.push(ext.TextAlign.configure({ types: ['heading', 'paragraph'] }));
        if (ext.TextStyle) configured.push(ext.TextStyle);
        if (ext.Color) configured.push(ext.Color);
        if (ext.Highlight) configured.push(ext.Highlight.configure({ multicolor: true }));
        if (ext.Subscript) configured.push(ext.Subscript);
        if (ext.Superscript) configured.push(ext.Superscript);
        if (ext.Table) configured.push(ext.Table.configure({ resizable: true }));
        if (ext.TableRow) configured.push(ext.TableRow);
        if (ext.TableCell) configured.push(ext.TableCell);
        if (ext.TableHeader) configured.push(ext.TableHeader);
        if (ext.TaskList) configured.push(ext.TaskList);
        if (ext.TaskItem) configured.push(ext.TaskItem.configure({ nested: true }));

        // Build DOM: wrap > toolbar + editorArea
        var wrapEl = document.createElement('div');
        wrapEl.className = 'kboard-tiptap-wrap';

        var editorArea = document.createElement('div');
        editorArea.className = 'kboard-tiptap-editor-area';

        editor = new core.Editor({
          element: editorArea,
          extensions: configured,
          content: initialHTML,
          onUpdate: function (ctx) {
            textarea.value = ctx.editor.getHTML();
            if (toolbarObj) toolbarObj.updateStates();
          },
          onSelectionUpdate: function () {
            if (toolbarObj) toolbarObj.updateStates();
          }
        });

        toolbarObj = buildToolbar(editor, ext);
        wrapEl.appendChild(toolbarObj.element);
        wrapEl.appendChild(editorArea);
        element.appendChild(wrapEl);

        textarea.value = editor.getHTML();
        toolbarObj.updateStates();
      },
      getHTML: function getHTML() {
        return editor ? editor.getHTML() : textarea.value || '';
      },
      setHTML: function setHTML(html) {
        var next = typeof html === 'string' ? html : '';
        if (editor) {
          editor.commands.setContent(next, false, { preserveWhitespace: 'full' });
        }
        textarea.value = next;
      },
      insertMedia: function insertMedia(url) {
        if (!url) return;
        var safeURL = String(url);
        if (!editor) {
          textarea.value += '<p><img src="' + registry.utils.escapeHTML(safeURL) + '" alt="" /></p>';
          return;
        }
        if (editor.commands.setImage) {
          editor.chain().focus().setImage({ src: safeURL }).run();
        } else {
          editor.chain().focus().insertContent('<p><img src="' + registry.utils.escapeHTML(safeURL) + '" alt="" /></p>').run();
        }
        textarea.value = editor.getHTML();
      },
      beforeSubmit: function beforeSubmit() {
        textarea.value = this.getHTML();
        return textarea.value;
      },
      destroy: function destroy() {
        if (editor) {
          editor.destroy();
          editor = null;
        }
        toolbarObj = null;
      }
    };
  });
})(window);
