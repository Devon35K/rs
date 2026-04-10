<?php
ob_start();
$canPost = in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty']);
?>

<!-- ── HERO ── -->
<div class="admin-hero" style="background-image: url('<?= BASE_URL ?>icon/backbird.png');">
    <div class="admin-hero-overlay"></div>
    <div class="admin-hero-content">
        <p class="admin-hero-eyebrow">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            BSIT Department
        </p>
        <h1 class="admin-hero-title">Announcements</h1>
        <p class="admin-hero-sub">Stay up-to-date with the latest news, memos, and updates from the institution.</p>
        <?php if ($canPost): ?>
        <button class="admin-hero-btn" onclick="openModal('announcementModal')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Post Announcement
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── ANNOUNCEMENTS GRID ── -->
<section class="admin-section">
    <div class="admin-section-header" style="flex-wrap:wrap; gap:0.75rem;">
        <div>
            <h2 class="admin-section-title">Recent Announcements</h2>
            <p class="admin-section-sub" id="announceCount"><?= count($announcements) ?> post<?= count($announcements) !== 1 ? 's' : '' ?> available</p>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <div class="memo-searchbox">
                <svg class="memo-searchbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="announceSearch" class="memo-searchbox-input" placeholder="Search announcements…" oninput="filterAnnouncements()">
                <button class="memo-searchbox-clear" id="announceSearchClear" onclick="clearAnnounceSearch()" title="Clear" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <?php if ($canPost): ?>
            <button class="admin-btn-primary" onclick="openModal('announcementModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Post
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($announcements)): ?>
    <div class="admin-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
        <p>No announcements yet.</p>
        <?php if ($canPost): ?>
        <button class="admin-btn-primary" onclick="openModal('announcementModal')">Post the first one</button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="announce-grid" id="announceGrid">
        <?php foreach ($announcements as $a): ?>
        <article class="announce-card"
            data-title="<?= strtolower(htmlspecialchars($a['title'])) ?>"
            data-body="<?= strtolower(htmlspecialchars(mb_substr($a['body'], 0, 300))) ?>"
            style="cursor: pointer; transition: transform 0.2s; position:relative;"
            onclick='openViewAnnouncementModal(<?= htmlspecialchars(json_encode($a), ENT_QUOTES, "UTF-8") ?>)'
            onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
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
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
            <div class="announce-actions" style="position:absolute; top:1rem; right:1rem; z-index:10;" onclick="event.stopPropagation()">
                <a href="index.php?page=announcements&action=delete&id=<?= $a['id'] ?>"
                   class="announce-delete"
                   style="background:white; padding:0.4rem; border-radius:6px; box-shadow:0 2px 5px rgba(0,0,0,0.2);"
                   onclick="return confirm('Delete this announcement?')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                </a>
            </div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ── NEW ANNOUNCEMENT MODAL ── -->
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

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

/* ── Announcement Search ── */
function filterAnnouncements() {
    const q = (document.getElementById('announceSearch')?.value || '').toLowerCase().trim();
    const clr = document.getElementById('announceSearchClear');
    if (clr) clr.style.display = q ? 'flex' : 'none';
    let visible = 0;
    document.querySelectorAll('.announce-card').forEach(card => {
        const match = !q || card.dataset.title.includes(q) || card.dataset.body.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const label = document.getElementById('announceCount');
    if (label) label.textContent = visible + ' post' + (visible !== 1 ? 's' : '') + ' available';
}
function clearAnnounceSearch() {
    document.getElementById('announceSearch').value = '';
    filterAnnouncements();
}

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

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php
$content   = ob_get_clean();
$pageTitle = 'Announcements — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
?>