<?php
ob_start();
$canPost        = in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty']);
$isAdmin        = ($_SESSION['user_role'] ?? '') === 'admin';

$catCounts      = [];
$internalCount  = 0;
$externalCount  = 0;
foreach ($memos as $m) {
    $cat = $m['category'] ?? 'General';
    $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
    $m['type'] === 'internal' ? $internalCount++ : $externalCount++;
}
$totalMemoCount = count($memos);

// Category → color map
$catColors = [
    'Academic'       => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'dot' => '#7c3aed'],
    'Administrative' => ['bg' => '#fef3c7', 'text' => '#92400e', 'dot' => '#d97706'],
    'Events'         => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
    'General'        => ['bg' => '#f0f0f6', 'text' => '#555',    'dot' => '#888'],
];
?>

<!-- ══════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════ -->
<div class="memo-hero">
    <div class="memo-hero-overlay"></div>
    <div class="memo-hero-content">
        <p class="memo-hero-eyebrow">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
            Official Records
        </p>
        <h1 class="memo-hero-title">Memoranda</h1>
        <p class="memo-hero-sub">Official memoranda — internal policies, directives, and external communications.</p>
    </div>

    <!-- KPI mini-stats -->
    <div class="memo-hero-stats">
        <div class="memo-hero-stat">
            <span class="memo-hero-stat-val"><?= $totalMemoCount ?></span>
            <span class="memo-hero-stat-lbl">Total</span>
        </div>
        <div class="memo-hero-stat-divider"></div>
        <div class="memo-hero-stat">
            <span class="memo-hero-stat-val"><?= $internalCount ?></span>
            <span class="memo-hero-stat-lbl">Internal</span>
        </div>
        <div class="memo-hero-stat-divider"></div>
        <div class="memo-hero-stat">
            <span class="memo-hero-stat-val"><?= $externalCount ?></span>
            <span class="memo-hero-stat-lbl">External</span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     TOOLBAR
══════════════════════════════════════════════════ -->
<div class="memo-toolbar">
    <!-- Left: filter chips -->
    <div class="memo-toolbar-chips" id="memoChips">
        <button class="memo-chip-btn memo-chip-active" data-filter="" onclick="setFilter(this,'')">
            All <span class="memo-chip-count"><?= $totalMemoCount ?></span>
        </button>
        <button class="memo-chip-btn" data-filter="internal" onclick="setFilter(this,'internal')">
            Internal <span class="memo-chip-count"><?= $internalCount ?></span>
        </button>
        <button class="memo-chip-btn" data-filter="external" onclick="setFilter(this,'external')">
            External <span class="memo-chip-count"><?= $externalCount ?></span>
        </button>
        <?php foreach ($catCounts as $cat => $cnt): ?>
        <button class="memo-chip-btn memo-chip-btn--cat" data-cat-filter="<?= strtolower(str_replace(' ','-',$cat)) ?>" onclick="setCatFilter(this,'<?= strtolower(str_replace(' ','-',$cat)) ?>')">
            <?= htmlspecialchars($cat) ?> <span class="memo-chip-count"><?= $cnt ?></span>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Right: search + new button -->
    <div class="memo-toolbar-right">
        <div class="memo-searchbox">
            <svg class="memo-searchbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="memoSearch" class="memo-searchbox-input" placeholder="Search subject or memo no…" oninput="filterMemos()">
            <button class="memo-searchbox-clear" id="memoSearchClear" onclick="clearSearch()" title="Clear">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <?php if ($canPost): ?>
        <button class="memo-new-btn" onclick="openMemoModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Memo
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     MEMO LIST
══════════════════════════════════════════════════ -->
<div class="memo-list-section">

    <?php if (empty($memos)): ?>
    <div class="memo-empty-state">
        <div class="memo-empty-icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3 class="memo-empty-title">No memoranda yet</h3>
        <p class="memo-empty-sub">Official memos will appear here once published.</p>
        <?php if ($canPost): ?>
        <button class="memo-new-btn" onclick="openMemoModal()" style="margin-top:.75rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add First Memo
        </button>
        <?php endif; ?>
    </div>

    <?php else: ?>

    <!-- Table header -->
    <div class="memo-list-header">
        <div class="mlh-memo">Memo No.</div>
        <div class="mlh-date">Date</div>
        <div class="mlh-subject">Subject</div>
        <div class="mlh-cat">Category</div>
        <div class="mlh-type">Type</div>
        <div class="mlh-doc">Document</div>
        <?php if ($isAdmin): ?><div class="mlh-act"></div><?php endif; ?>
    </div>

    <!-- Rows -->
    <div class="memo-list" id="memoList">
    <?php foreach ($memos as $i => $memo):
        $type    = $memo['type'] ?? 'internal';
        $cat     = $memo['category'] ?? 'General';
        $catSlug = strtolower(str_replace(' ', '-', $cat));
        $clr     = $catColors[$cat] ?? $catColors['General'];
    ?>
    <div class="memo-item memo-item--<?= $type ?>"
         data-subject="<?= strtolower(htmlspecialchars($memo['subject'])) ?>"
         data-memo="<?= strtolower(htmlspecialchars($memo['memo_no'])) ?>"
         data-type="<?= $type ?>"
         data-cat="<?= $catSlug ?>"
         style="animation-delay:<?= $i * 0.03 ?>s; cursor:pointer;"
         onclick='openViewMemoModal(<?= htmlspecialchars(json_encode($memo), ENT_QUOTES, "UTF-8") ?>)'>

        <!-- Left accent line (set by CSS per type) -->

        <!-- Memo No. -->
        <div class="mi-memo">
            <span class="mi-memo-no"><?= htmlspecialchars($memo['memo_no']) ?></span>
        </div>

        <!-- Date -->
        <div class="mi-date">
            <span class="mi-date-day"><?= date('d', strtotime($memo['date_issued'])) ?></span>
            <span class="mi-date-mon"><?= date('M Y', strtotime($memo['date_issued'])) ?></span>
        </div>

        <!-- Subject + author -->
        <div class="mi-subject">
            <span class="mi-subject-text"><?= htmlspecialchars($memo['subject']) ?></span>
            <span class="mi-author">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                <?= htmlspecialchars($memo['author'] ?? '—') ?>
            </span>
        </div>

        <!-- Category -->
        <div class="mi-cat">
            <span class="mi-cat-dot" style="background:<?= $clr['dot'] ?>;"></span>
            <span class="mi-cat-label" style="color:<?= $clr['text'] ?>;"><?= htmlspecialchars($cat) ?></span>
        </div>

        <!-- Type -->
        <div class="mi-type">
            <span class="mi-type-badge mi-type-badge--<?= $type ?>"><?= ucfirst($type) ?></span>
        </div>

        <!-- Document link -->
        <div class="mi-doc" onclick="event.stopPropagation()">
            <?php if (!empty($memo['link'])): ?>
                <a href="<?= htmlspecialchars($memo['link']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    View
                </a>
            <?php elseif (!empty($memo['file_path'])): ?>
                <a href="<?= htmlspecialchars($memo['file_path']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--file">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download
                </a>
            <?php else: ?>
                <span class="mi-doc-none">—</span>
            <?php endif; ?>
        </div>

        <!-- Delete (admin only) -->
        <?php if ($isAdmin): ?>
        <div class="mi-act" onclick="event.stopPropagation()">
            <a href="index.php?page=memo&action=delete&id=<?= $memo['id'] ?>"
               class="mi-del"
               onclick="return confirm('Permanently delete this memo?')"
               title="Delete memo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </a>
        </div>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
    </div>

    <!-- Footer count -->
    <div class="memo-list-footer">
        <span id="memoCount"><?= $totalMemoCount ?> memo<?= $totalMemoCount !== 1 ? 's' : '' ?></span>
    </div>

    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════
     NEW MEMO MODAL
══════════════════════════════════════════════════ -->
<?php if ($canPost): ?>
<div id="memoModal" class="mm-overlay" role="dialog" aria-modal="true" aria-labelledby="mmTitle">
    <div class="mm-box">

        <div class="mm-header">
            <div class="mm-header-left">
                <div class="mm-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <h2 class="mm-title" id="mmTitle">New Memorandum</h2>
                    <p class="mm-subtitle">Complete all required fields marked with <span style="color:#ffa0a0;">*</span></p>
                </div>
            </div>
            <button class="mm-close" onclick="closeMemoModal()" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="index.php?page=memo&action=store" enctype="multipart/form-data">
            <div class="mm-body">

                <!-- Row 1 -->
                <div class="mm-row-2">
                    <div class="mm-field">
                        <label class="mm-label" for="mm_memo_no">Memo No. <span class="mm-req">*</span></label>
                        <input class="mm-input" type="text" id="mm_memo_no" name="memo_no" placeholder="MEMO-2025-001" required>
                    </div>
                    <div class="mm-field">
                        <label class="mm-label" for="mm_date">Date Issued <span class="mm-req">*</span></label>
                        <input class="mm-input" type="date" id="mm_date" name="date_issued" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Subject -->
                <div class="mm-field">
                    <label class="mm-label" for="mm_subject">Subject <span class="mm-req">*</span></label>
                    <input class="mm-input" type="text" id="mm_subject" name="subject" placeholder="Enter the memorandum subject…" required>
                </div>

                <!-- Row 2 -->
                <div class="mm-row-2">
                    <div class="mm-field">
                        <label class="mm-label" for="mm_category">Category</label>
                        <select class="mm-select" id="mm_category" name="category">
                            <option value="Academic">Academic</option>
                            <option value="Administrative">Administrative</option>
                            <option value="Events">Events</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    <div class="mm-field">
                        <label class="mm-label" for="mm_type">Type</label>
                        <div class="mm-type-toggle" id="mmTypeToggle">
                            <button type="button" class="mm-toggle-btn mm-toggle-active" data-val="internal" onclick="setType('internal')">Internal</button>
                            <button type="button" class="mm-toggle-btn" data-val="external" onclick="setType('external')">External</button>
                            <input type="hidden" name="type" id="mm_type_hidden" value="internal">
                        </div>
                    </div>
                </div>

                <!-- Link -->
                <div class="mm-field">
                    <label class="mm-label" for="mm_link">
                        External Link
                        <span class="mm-hint">optional</span>
                    </label>
                    <div class="mm-icon-input">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <input class="mm-input mm-input--icon" type="url" id="mm_link" name="link" placeholder="https://…">
                    </div>
                </div>

                <!-- File upload -->
                <div class="mm-field">
                    <label class="mm-label">Attachment <span class="mm-hint">optional — PDF, DOCX</span></label>
                    <label class="mm-dropzone" for="mm_file" id="mmDropzone">
                        <div class="mm-dropzone-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span class="mm-dropzone-label" id="mm_file_name">Click to choose file or drag & drop</span>
                            <span class="mm-dropzone-hint">PDF, DOCX up to 10 MB</span>
                        </div>
                        <input type="file" id="mm_file" name="file" class="mm-file-hidden" onchange="updateFileName(this)">
                    </label>
                </div>

            </div>

            <div class="mm-footer">
                <button type="button" class="mm-btn-cancel" onclick="closeMemoModal()">Cancel</button>
                <button type="submit" class="mm-btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    Publish Memo
                </button>
            </div>
        </form>
</div>
<?php endif; ?>

<!-- ── VIEW MEMO MODAL ── -->
<div id="viewMemoModal" class="modal-overlay" style="display:none; z-index:9999;">
    <div class="modal-box" style="max-width: 900px; width:95%; height:90vh; padding:0; display:flex; flex-direction:column; overflow:hidden;">
        <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:1.5rem;">
            <div>
                <div style="font-size:0.85rem; color:#64748b; margin-bottom:0.3rem;"><span id="vmMemoNo"></span> • <span id="vmDate"></span></div>
                <h3 id="vmSubject" style="margin:0; color:#0f172a; font-size:1.3rem;"></h3>
            </div>
            <button onclick="closeModal('viewMemoModal')" class="modal-close">×</button>
        </div>
        <div style="flex:1; background:#e2e8f0; display:flex; align-items:center; justify-content:center; position:relative;">
            <iframe id="vmIframe" src="" style="width:100%; height:100%; border:none; display:none;"></iframe>
            <div id="vmNoPreview" style="display:none; text-align:center; padding:2rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" style="margin-bottom:1rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <h3 style="color:#334155; margin-bottom:0.5rem;">No Preview Available</h3>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:1rem;">This type of file cannot be previewed directly in the browser.</p>
                <a id="vmDownloadBtn" href="" class="admin-btn-primary" download style="display:inline-block; text-decoration:none;">Download File</a>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    let mod = document.getElementById(id);
    if(mod) mod.style.display = 'flex';
}
function closeModal(id) {
    let mod = document.getElementById(id);
    if(mod) mod.style.display = 'none';
}

function openViewMemoModal(data) {
    document.getElementById('vmSubject').textContent = data.subject;
    document.getElementById('vmMemoNo').textContent = data.memo_no;
    document.getElementById('vmDate').textContent = new Date(data.date_issued).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    
    let iframe = document.getElementById('vmIframe');
    let noPreview = document.getElementById('vmNoPreview');
    let downloadBtn = document.getElementById('vmDownloadBtn');
    
    iframe.style.display = 'none';
    noPreview.style.display = 'none';

    if (data.link) { // Google Drive
        iframe.src = data.link;
        iframe.style.display = 'block';
    } else if (data.file_path) {
        let isPdf = data.file_path.toLowerCase().endsWith('.pdf');
        let fullPath = '<?= BASE_URL ?>' + data.file_path;
        if (isPdf) {
            iframe.src = fullPath;
            iframe.style.display = 'block';
        } else {
            downloadBtn.href = fullPath;
            noPreview.style.display = 'block';
        }
    } else {
        noPreview.style.display = 'block';
        downloadBtn.style.display = 'none';
    }
    
    openModal('viewMemoModal');
}

/* ── State ── */
let activeType = '';
let activeCat  = '';

/* ── Type filter chip ── */
function setFilter(el, type) {
    activeCat = '';
    activeType = type;
    document.querySelectorAll('.memo-toolbar-chips .memo-chip-btn').forEach(b => b.classList.remove('memo-chip-active'));
    el.classList.add('memo-chip-active');
    filterMemos();
}

function setCatFilter(el, cat) {
    activeType = '';
    activeCat  = cat;
    document.querySelectorAll('.memo-toolbar-chips .memo-chip-btn').forEach(b => b.classList.remove('memo-chip-active'));
    el.classList.add('memo-chip-active');
    filterMemos();
}

/* ── Search + filter ── */
function filterMemos() {
    const q   = (document.getElementById('memoSearch')?.value || '').toLowerCase().trim();
    const clr = document.getElementById('memoSearchClear');
    if (clr) clr.style.display = q ? 'flex' : 'none';

    let visible = 0;
    document.querySelectorAll('.memo-item').forEach(row => {
        const matchQ    = !q || row.dataset.subject.includes(q) || row.dataset.memo.includes(q);
        const matchType = !activeType || row.dataset.type === activeType;
        const matchCat  = !activeCat  || row.dataset.cat  === activeCat;
        const show      = matchQ && matchType && matchCat;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const label = document.getElementById('memoCount');
    if (label) label.textContent = visible + ' memo' + (visible !== 1 ? 's' : '');
}

function clearSearch() {
    document.getElementById('memoSearch').value = '';
    filterMemos();
}

/* ── Modal ── */
function openMemoModal() {
    const m = document.getElementById('memoModal');
    if (!m) return;
    m.classList.add('mm-open');
    document.body.style.overflow = 'hidden';
}
function closeMemoModal() {
    const m = document.getElementById('memoModal');
    if (!m) return;
    m.classList.remove('mm-open');
    document.body.style.overflow = '';
}
document.getElementById('memoModal')?.addEventListener('click', function(e){ if(e.target===this) closeMemoModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMemoModal(); });

/* ── Type toggle inside modal ── */
function setType(val) {
    document.getElementById('mm_type_hidden').value = val;
    document.querySelectorAll('.mm-toggle-btn').forEach(b => {
        b.classList.toggle('mm-toggle-active', b.dataset.val === val);
    });
}

/* ── File drop zone ── */
function updateFileName(input) {
    const name = document.getElementById('mm_file_name');
    if (input.files[0]) {
        name.textContent = input.files[0].name;
        document.getElementById('mmDropzone').classList.add('mm-dropzone--selected');
    } else {
        name.textContent = 'Click to choose file or drag & drop';
        document.getElementById('mmDropzone').classList.remove('mm-dropzone--selected');
    }
}

/* ── Init ── */
document.getElementById('memoSearchClear').style.display = 'none';
</script>

<?php
$content   = ob_get_clean();
$pageTitle = 'Memoranda — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
