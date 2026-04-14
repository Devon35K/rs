<?php
ob_start();
$canUpload = in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty']);
?>

<!-- ── HEADER ── -->
<div class="admin-hero" style="background-image: url('<?= BASE_URL ?>icon/backbird.png'); padding: 3rem 2rem;">
    <div class="admin-hero-overlay"></div>
    <div class="admin-hero-content">
        <h1 class="admin-hero-title">Central File Repository</h1>
        <p class="admin-hero-sub">Secure access to Department, Faculty, and Student requirements and documents.</p>
        <div style="margin-top:20px; display: flex; gap: 10px; justify-content: center;">
            <button class="admin-hero-btn" onclick="openModal('uploadModal')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Upload File or Drive Link
            </button>
        </div>
    </div>
</div>

<!-- ── UPLOADS GRID ── -->
<section class="admin-section" style="margin-top:2rem;">
    <div class="memo-toolbar">
        <div class="memo-toolbar-chips">
            <button class="memo-chip-btn memo-chip-active" onclick="filterDocs('All', this)">All Documents</button>
            <button class="memo-chip-btn" onclick="filterDocs('Department', this)">Department</button>
            <button class="memo-chip-btn" onclick="filterDocs('Faculty', this)">Faculty</button>
            <button class="memo-chip-btn" onclick="filterDocs('Student', this)">Student</button>
        </div>
        <div class="memo-toolbar-right">
            <div class="memo-searchbox">
                <svg class="memo-searchbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="docSearch" class="memo-searchbox-input" placeholder="Search documents…" oninput="filterDocs(activeDocCat, null)">
                <button class="memo-searchbox-clear" id="docSearchClear" onclick="clearDocSearch()" title="Clear" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="announce-grid" id="docsGrid">
        <?php if (empty($documents)): ?>
            <div class="memo-empty-state" style="grid-column: 1 / -1;">
                <h3 class="memo-empty-title">No documents uploaded yet</h3>
                <p class="memo-empty-sub">Files and Google Drive links will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($documents as $doc): ?>
            <article class="announce-card doc-card"
                data-category="<?= htmlspecialchars($doc['category'] ?? 'Department') ?>"
                data-title="<?= strtolower(htmlspecialchars($doc['title'])) ?>">
                <div class="announce-body" style="padding: 1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 1rem;">
                        <?php if (!empty($doc['drive_link'])): ?>
                            <div style="background: rgba(15,159,103,0.1); color: #0f9f67; padding: 0.5rem; border-radius: 8px;" title="Google Drive Link">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                        <?php else: ?>
                            <div style="background: rgba(139,0,0,0.1); color: #8B0000; padding: 0.5rem; border-radius: 8px;" title="Locally Stored File">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                        <?php endif; ?>
                        
                        <span class="announce-audience-badge" style="position:static; margin:0;"><?= htmlspecialchars($doc['category'] ?? 'Department') ?></span>
                    </div>

                    <h3 class="announce-title" style="margin-bottom:0.5rem; font-size:1.1rem; line-height:1.4"><?= htmlspecialchars($doc['title']) ?></h3>
                    
                    <div class="announce-meta" style="margin-top:1rem; border-top: 1px solid #eee; padding-top:1rem; display:flex; justify-content:space-between; align-items:center;">
                        <span class="announce-date"><?= date('M d, Y', strtotime($doc['created_at'])) ?></span>
                        
                        <?php if (!empty($doc['drive_link'])): ?>
                            <a href="<?= htmlspecialchars($doc['drive_link']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--link">Open Link</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL . 'public/' . htmlspecialchars($doc['file_path']) ?>" target="_blank" class="mi-doc-btn mi-doc-btn--file">Download</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($canUpload): ?>
                         <div style="margin-top:0.5rem; text-align:right;">
                             <a href="index.php?page=upload&action=delete&id=<?= $doc['id'] ?>" style="color:#ef4444; font-size:0.8rem; text-decoration:none;" onclick="return confirm('Delete this document?')">Delete</a>
                         </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- UPLOAD MODAL -->
<?php if ($canUpload): ?>
<div id="uploadModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Upload Document or Drive Link</h3>
            <button type="button" onclick="closeModal('uploadModal')" class="modal-close">×</button>
        </div>
        <form method="POST" action="index.php?page=upload&action=store" enctype="multipart/form-data">
            <div class="form-group">
                <label>Document Title *</label>
                <input type="text" name="title" required placeholder="Enter document title...">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="Department">Department</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Student">Student</option>
                </select>
            </div>
            
            <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.85rem; color: #334155; margin-bottom: 1rem; line-height: 1.5; text-align:justify;">
                    <strong>Security Note:</strong> To ensure data is secured within the institution's localized network, you must upload EITHER a physical file (stored privately on this server) OR provide a secure Google Drive Link (protected by your G-Suite settings). 
                </div>
                
                <div class="form-group">
                    <label>Physical Local File</label>
                    <input type="file" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg">
                </div>
                
                <div style="text-align:center; font-size:0.8rem; color:#94a3b8; font-weight:600; letter-spacing:1px; margin-bottom:0.5rem;">— OR —</div>
                
                <div class="form-group" style="margin-bottom:0;">
                    <label>Google Drive Link</label>
                    <input type="url" name="drive_link" placeholder="https://drive.google.com/...">
                </div>
            </div>

            <!-- Audience is explicitly set to all, security is now managed by Google Drive access or local network restrictions -->
            <input type="hidden" name="audience" value="all">

            <div class="modal-footer">
                <button type="button" onclick="closeModal('uploadModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Post Document</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
let activeDocCat = 'All';

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function filterDocs(cat, btn) {
    if (cat !== null) activeDocCat = cat;
    const q = (document.getElementById('docSearch')?.value || '').toLowerCase().trim();
    const clr = document.getElementById('docSearchClear');
    if (clr) clr.style.display = q ? 'flex' : 'none';
    // Styling chips
    if (btn) {
        document.querySelectorAll('.memo-chip-btn').forEach(b => b.classList.remove('memo-chip-active'));
        btn.classList.add('memo-chip-active');
    }
    // Filtering
    document.querySelectorAll('.doc-card').forEach(card => {
        const matchCat = activeDocCat === 'All' || card.dataset.category === activeDocCat;
        const matchQ   = !q || card.dataset.title.includes(q);
        card.style.display = (matchCat && matchQ) ? '' : 'none';
    });
}
function clearDocSearch() {
    document.getElementById('docSearch').value = '';
    filterDocs(activeDocCat, null);
}
window.onclick = function(event) {
    let mod = document.getElementById('uploadModal');
    if (event.target == mod) mod.style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Requirements — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
