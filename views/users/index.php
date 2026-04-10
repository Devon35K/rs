<?php
ob_start();
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$totalUsers = count($users);
?>

<!-- ══════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════ -->
<div class="memo-hero">
    <div class="memo-hero-overlay"></div>
    <div class="memo-hero-content">
        <p class="memo-hero-eyebrow">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            System Administration
        </p>
        <h1 class="memo-hero-title">Staff Account Management</h1>
        <p class="memo-hero-sub">Manage faculty, student, and administrator accounts across the system.</p>
    </div>

    <!-- KPI mini-stats -->
    <div class="memo-hero-stats">
        <div class="memo-hero-stat">
            <span class="memo-hero-stat-val"><?= $totalUsers ?></span>
            <span class="memo-hero-stat-lbl">Total</span>
        </div>
    </div>
</div>

<!-- TOOLBAR -->
<div class="memo-toolbar">
    <div class="memo-toolbar-right" style="width:100%; justify-content:space-between;">
        <div class="memo-searchbox">
            <svg class="memo-searchbox-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="userSearch" class="memo-searchbox-input" placeholder="Search by name or email…" oninput="filterUsers()">
            <button class="memo-searchbox-clear" id="userSearchClear" onclick="clearUserSearch()" title="Clear" style="display:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <?php if ($isAdmin): ?>
        <button class="memo-new-btn" onclick="openUserModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Account
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     USER LIST
══════════════════════════════════════════════════ -->
<div class="memo-list-section">

    <?php if (empty($users)): ?>
    <div class="memo-empty-state">
        <div class="memo-empty-icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3 class="memo-empty-title">No users found</h3>
        <p class="memo-empty-sub">Add accounts to see them listed here.</p>
    </div>
    <?php else: ?>

    <div class="va-panel" style="margin-top: 1rem; border: none; box-shadow: none;">
        <div class="va-panel-body va-panel-body--flush">
            <div class="va-visits-table-wrap">
                <table class="va-visits-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Date Joined</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                    <?php foreach ($users as $u): ?>
                        <tr data-name="<?= strtolower(htmlspecialchars($u['name'])) ?>" data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>">
                            <td>
                                <div class="va-visit-user">
                                    <div class="va-visit-avatar"><?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?></div>
                                    <div class="va-visit-name"><?= htmlspecialchars($u['name']) ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="va-role-badge va-role-badge--<?= $u['role'] ?>">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td class="va-visit-time"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td style="text-align: right;">
                                <?php if ($u['id'] !== ($_SESSION['user_id'] ?? 0)): ?>
                                    <a href="index.php?page=users&action=delete&id=<?= $u['id'] ?>"
                                       class="mi-del"
                                       style="color: var(--crimson, #8b0000);"
                                       onclick="return confirm('Permanently delete this account?')"
                                       title="Delete account">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════
     NEW USER MODAL
══════════════════════════════════════════════════ -->
<?php if ($isAdmin): ?>
<div id="userModal" class="mm-overlay" role="dialog" aria-modal="true" aria-labelledby="umTitle">
    <div class="mm-box">

        <div class="mm-header">
            <div class="mm-header-left">
                <div class="mm-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <h2 class="mm-title" id="umTitle">New Staff Account</h2>
                    <p class="mm-subtitle">Complete all required fields marked with <span style="color:#ffa0a0;">*</span></p>
                </div>
            </div>
            <button class="mm-close" onclick="closeUserModal()" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="index.php?page=users&action=store">
            <div class="mm-body">
                <div class="mm-row-2">
                    <div class="mm-field">
                        <label class="mm-label" for="um_name">Full Name <span class="mm-req">*</span></label>
                        <input class="mm-input" type="text" id="um_name" name="name" placeholder="Juan Dela Cruz" required>
                    </div>
                </div>

                <div class="mm-row-2">
                    <div class="mm-field">
                        <label class="mm-label" for="um_email">Email Address <span class="mm-req">*</span></label>
                        <input class="mm-input" type="email" id="um_email" name="email" placeholder="juan@school.edu" required>
                    </div>
                    <div class="mm-field">
                        <label class="mm-label" for="um_password">Initial Password <span class="mm-req">*</span></label>
                        <input class="mm-input" type="password" id="um_password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mm-field">
                    <label class="mm-label" for="um_role">Account Role <span class="mm-req">*</span></label>
                    <select class="mm-select" id="um_role" name="role" required>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>

            <div class="mm-footer">
                <button type="button" class="mm-btn-cancel" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="mm-btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function filterUsers() {
    const q = (document.getElementById('userSearch')?.value || '').toLowerCase().trim();
    const clr = document.getElementById('userSearchClear');
    if (clr) clr.style.display = q ? 'flex' : 'none';
    document.querySelectorAll('#userTableBody tr').forEach(row => {
        const match = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        row.style.display = match ? '' : 'none';
    });
}
function clearUserSearch() {
    document.getElementById('userSearch').value = '';
    filterUsers();
}

function openUserModal() {
    const m = document.getElementById('userModal');
    if (!m) return;
    m.classList.add('mm-open');
    document.body.style.overflow = 'hidden';
}
function closeUserModal() {
    const m = document.getElementById('userModal');
    if (!m) return;
    m.classList.remove('mm-open');
    document.body.style.overflow = '';
}
document.getElementById('userModal')?.addEventListener('click', function(e){ if(e.target===this) closeUserModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserModal(); });
</script>

<?php
$content   = ob_get_clean();
$pageTitle = 'Account Management — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
