<?php
ob_start();
$canPost = in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty']);
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

// Memos setup
$catCounts      = [];
$internalCount  = 0;
$externalCount  = 0;
foreach ($memos as $m) {
    $cat = $m['category'] ?? 'General';
    $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
    $m['type'] === 'internal' ? $internalCount++ : $externalCount++;
}
$totalMemoCount = count($memos);

$catColors = [
    'Academic'       => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'dot' => '#7c3aed'],
    'Administrative' => ['bg' => '#fef3c7', 'text' => '#92400e', 'dot' => '#d97706'],
    'Events'         => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#16a34a'],
    'General'        => ['bg' => '#f0f0f6', 'text' => '#555',    'dot' => '#888'],
];
?>

<!-- ── HERO ── -->
<div class="admin-hero" style="background-image: url('<?= BASE_URL ?>icon/backbird.png');">
    <div class="admin-hero-overlay"></div>
    <div class="admin-hero-content">
        <p class="admin-hero-eyebrow">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            BSIT Department
        </p>
        <h1 class="admin-hero-title">Dashboard</h1>
        <p class="admin-hero-sub">Stay up-to-date with the latest news, memos, and updates from the institution.</p>
        <?php if ($canPost): ?>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;">
            <button class="admin-hero-btn" onclick="openModal('announcementModal')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Post Announcement
            </button>
            <button class="admin-hero-btn" onclick="openMemoModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Memo
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── ANNOUNCEMENTS GRID ── -->
<section class="admin-section" style="margin-top:2rem;">
    <div class="admin-section-header">
        <div>
            <h2 class="admin-section-title">Recent Announcements</h2>
            <p class="admin-section-sub"><?= count($announcements) ?> post<?= count($announcements) !== 1 ? 's' : '' ?> available</p>
        </div>
    </div>

    <?php if (empty($announcements)): ?>
    <div class="admin-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
        <p>No announcements yet.</p>
    </div>
    <?php else: ?>
    <div class="announce-grid">
        <?php foreach (array_slice($announcements, 0, 4) as $a): ?>
        <article class="announce-card" style="margin-bottom:1rem; cursor: pointer; transition: transform 0.2s; position:relative;" onclick='openViewAnnouncementModal(<?= htmlspecialchars(json_encode($a), ENT_QUOTES, "UTF-8") ?>)' onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="announce-thumb">
                <?php if (!empty($a['cover_image'])): ?>
                <img src="<?= BASE_URL . 'public/' . htmlspecialchars($a['cover_image']) ?>" alt="">
                <?php else: ?>
                <div class="announce-thumb-placeholder">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                </div>
                <?php endif; ?>
                <span class="announce-audience-badge audience-<?= $a['audience'] ?>"><?= ucfirst($a['audience']) ?></span>
            </div>
            <div class="announce-body">
                <h3 class="announce-title"><?= htmlspecialchars($a['title']) ?></h3>
                <p class="announce-excerpt"><?= htmlspecialchars(mb_substr($a['body'], 0, 120)) . (mb_strlen($a['body']) > 120 ? '…' : '') ?></p>
                <div class="announce-meta">
                    <span class="announce-author">
                        <span class="announce-author-avatar"><?= strtoupper(substr($a['author_name'] ?? 'A', 0, 1)) ?></span>
                        <?= htmlspecialchars($a['author_name'] ?? 'Admin') ?>
                    </span>
                    <span class="announce-date"><?= date('M d, Y', strtotime($a['created_at'])) ?></span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ── MEMOS LIST ── -->
<section class="admin-section" style="margin-top:2rem;">
    <div class="admin-section-header">
        <div>
            <h2 class="admin-section-title">Official Memoranda</h2>
            <p class="admin-section-sub"><?= $totalMemoCount ?> memos available</p>
        </div>
    </div>

    <!-- Memos Toolbar -->
    <div class="memo-toolbar">
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
        </div>

        <div class="memo-toolbar-right">
            <div class="memo-searchbox">
                <svg class="memo-searchbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="memoSearch" class="memo-searchbox-input" placeholder="Search subject or memo no…" oninput="filterMemos()">
                <button class="memo-searchbox-clear" id="memoSearchClear" onclick="clearSearch()" title="Clear">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="memo-list-section">
        <?php if (empty($memos)): ?>
        <div class="memo-empty-state">
            <h3 class="memo-empty-title">No memoranda yet</h3>
            <p class="memo-empty-sub">Official memos will appear here once published.</p>
        </div>
        <?php else: ?>
        <div class="memo-list-header">
            <div class="mlh-memo">Memo No.</div>
            <div class="mlh-date">Date</div>
            <div class="mlh-subject">Subject</div>
            <div class="mlh-cat">Category</div>
            <div class="mlh-type">Type</div>
            <div class="mlh-doc">Document</div>
        </div>

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

            <div class="mi-memo"><span class="mi-memo-no"><?= htmlspecialchars($memo['memo_no']) ?></span></div>
            <div class="mi-date">
                <span class="mi-date-day"><?= date('d', strtotime($memo['date_issued'])) ?></span>
                <span class="mi-date-mon"><?= date('M Y', strtotime($memo['date_issued'])) ?></span>
            </div>
            <div class="mi-subject">
                <span class="mi-subject-text"><?= htmlspecialchars($memo['subject']) ?></span>
                <span class="mi-author">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    <?= htmlspecialchars($memo['author'] ?? '—') ?>
                </span>
            </div>
            <div class="mi-cat">
                <span class="mi-cat-dot" style="background:<?= $clr['dot'] ?>;"></span>
                <span class="mi-cat-label" style="color:<?= $clr['text'] ?>;"><?= htmlspecialchars($cat) ?></span>
            </div>
            <div class="mi-type"><span class="mi-type-badge mi-type-badge--<?= $type ?>"><?= ucfirst($type) ?></span></div>
            <div class="mi-doc" onclick="event.stopPropagation()">
                <?php if (!empty($memo['link'])): ?>
                    <a href="<?= htmlspecialchars($memo['link']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--link">View</a>
                <?php elseif (!empty($memo['file_path'])): ?>
                    <a href="<?= BASE_URL . 'public/' . htmlspecialchars($memo['file_path']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--file">Download</a>
                <?php else: ?>
                    <span class="mi-doc-none">—</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- MODALS -->

<!-- New Announcement Modal -->
<?php if ($canPost): ?>
<div id="announcementModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Post Announcement</h3>
            <button onclick="closeModal('announcementModal')" class="modal-close">×</button>
        </div>
        <form method="POST" action="index.php?page=announcements&action=store" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="Announcement title">
            </div>
            <div class="form-group">
                <label>Body *</label>
                <textarea name="body" rows="5" required placeholder="Write the announcement here..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Audience</label>
                    <select name="audience">
                        <option value="all">Everyone</option>
                        <option value="faculty">Faculty Only</option>
                        <option value="student">Students Only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('announcementModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Post Announcement</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- New Memo Modal -->
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
            <button type="button" class="mm-close" onclick="closeMemoModal()" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="index.php?page=memo&action=store" enctype="multipart/form-data">
            <div class="mm-body">
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
                <div class="mm-field">
                    <label class="mm-label" for="mm_subject">Subject <span class="mm-req">*</span></label>
                    <input class="mm-input" type="text" id="mm_subject" name="subject" placeholder="Enter the memorandum subject…" required>
                </div>
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
                <div class="mm-field">
                    <label class="mm-label" for="mm_link">External Link <span class="mm-hint">optional</span></label>
                    <div class="mm-icon-input">
                        <input class="mm-input mm-input--icon" style="padding-left:1rem" type="url" id="mm_link" name="link" placeholder="https://…">
                    </div>
                </div>
                <div class="mm-field">
                    <label class="mm-label">Attachment <span class="mm-hint">optional — PDF, DOCX</span></label>
                    <label class="mm-dropzone" for="mm_file" id="mmDropzone" style="margin-top:0.5rem;">
                        <div class="mm-dropzone-inner" style="padding:1rem;">
                            <span class="mm-dropzone-label" id="mm_file_name">Click to choose file</span>
                        </div>
                        <input type="file" id="mm_file" name="file" class="mm-file-hidden" onchange="updateFileName(this)">
                    </label>
                </div>
            </div>
            <div class="mm-footer">
                <button type="button" class="mm-btn-cancel" onclick="closeMemoModal()">Cancel</button>
                <button type="submit" class="mm-btn-submit">Publish Memo</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── VIEW ANNOUNCEMENT MODAL ── -->
<div id="viewAnnouncementModal" class="modal-overlay" style="display:none; z-index:9999;">
    <div class="modal-box" style="max-width: 700px; padding:0; overflow:hidden;">
        <div style="position:relative;">
            <img id="vaCover" src="" style="width:100%; height:250px; object-fit:cover; display:none;">
            <div id="vaPlaceholder" style="width:100%; height:120px; background:linear-gradient(135deg, #8B0000 0%, #4a0000 100%);"></div>
            <button onclick="closeModal('viewAnnouncementModal')" class="modal-close" style="position:absolute; top:15px; right:15px; background:rgba(0,0,0,0.5); color:white; border-radius:50%; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer;">×</button>
        </div>
        <div style="padding: 2.5rem; max-height:60vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <span id="vaAudience" class="announce-audience-badge"></span>
                <span id="vaDate" style="color:#64748b; font-size:0.85rem;"></span>
            </div>
            <h2 id="vaTitle" style="margin-top:0; margin-bottom:0.5rem; font-family:'Playfair Display', serif; color:#0f172a; font-size:1.8rem; line-height:1.3;"></h2>
            <div style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                <span id="vaAuthor"></span>
            </div>
            <div id="vaBody" style="color:#334155; line-height:1.7; font-size:1.05rem; white-space:pre-wrap;"></div>
        </div>
    </div>
</div>

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
// Announcements scripts
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}

// Viewer specific functions
function openViewAnnouncementModal(data) {
    document.getElementById('vaTitle').textContent = data.title;
    document.getElementById('vaBody').textContent = data.body;
    document.getElementById('vaAuthor').textContent = data.author_name || 'Admin';
    if(data.audience) {
        document.getElementById('vaAudience').textContent = data.audience.charAt(0).toUpperCase() + data.audience.slice(1);
    }
    document.getElementById('vaDate').textContent = new Date(data.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    if (data.cover_image) {
        document.getElementById('vaCover').src = '<?= BASE_URL ?>public/' + data.cover_image;
        document.getElementById('vaCover').style.display = 'block';
        document.getElementById('vaPlaceholder').style.display = 'none';
    } else {
        document.getElementById('vaCover').style.display = 'none';
        document.getElementById('vaPlaceholder').style.display = 'block';
    }
    
    openModal('viewAnnouncementModal');
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
        let fullPath = '<?= BASE_URL ?>public/' + data.file_path;
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

// Memos scripts
let activeType = '';
let activeCat  = '';

function setFilter(el, type) {
    activeCat = '';
    activeType = type;
    document.querySelectorAll('.memo-toolbar-chips .memo-chip-btn').forEach(b => b.classList.remove('memo-chip-active'));
    el.classList.add('memo-chip-active');
    filterMemos();
}

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
}

function clearSearch() {
    document.getElementById('memoSearch').value = '';
    filterMemos();
}

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

function setType(val) {
    document.getElementById('mm_type_hidden').value = val;
    document.querySelectorAll('.mm-toggle-btn').forEach(b => {
        b.classList.toggle('mm-toggle-active', b.dataset.val === val);
    });
}

function updateFileName(input) {
    const name = document.getElementById('mm_file_name');
    if (input.files[0]) {
        name.textContent = input.files[0].name;
    } else {
        name.textContent = 'Click to choose file';
    }
}
</script>

<?php
$content   = ob_get_clean();
$pageTitle = 'Dashboard — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
?>
