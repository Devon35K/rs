<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BSIT Department') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="admin-body">

<?php $isGuest = empty($_SESSION['user_id']); ?>
<?php if (!$isGuest): ?>

<!-- ── TOPBAR ── -->
<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <div class="topbar-brand">
            <span class="topbar-logo">🦅</span>
            <span class="topbar-name">BSIT Department</span>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Flash success inline indicator -->
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="topbar-flash">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); endif; ?>
        
        <!-- Google Drive Persistence -->
        <?php 
        if ($_SESSION['user_role'] === 'admin'): 
            $drive = new \Services\GoogleDriveService();
            if ($drive->isReady()):
                $currentPage = $_GET['page'] ?? 'announcements';
                // Map page to action
                $syncAction = ($currentPage === 'announcements') ? 'announcements&action=sync' : (($currentPage === 'memo') ? 'memo&action=sync' : 'upload&action=sync');
        ?>
                <div style="display:flex; align-items:center; gap:12px; margin-right:20px; padding-right:20px; border-right:1px solid #e2e8f0;">
                    <div title="Google Drive Connected" style="display:flex; align-items:center; background: #dcfce7; color: #166534; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; gap:6px;">
                        <span style="display:inline-block; width:6px; height:6px; background:#22c55e; border-radius:50%;"></span>
                        Drive Active
                    </div>
                    <a href="index.php?page=<?= $syncAction ?>" class="topbar-sync-btn" title="Sync current module with Drive">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        <span>Sync Drive</span>
                    </a>
                </div>
            <?php else: ?>
                <div title="Authorization Pending" style="display:flex; align-items:center; gap:12px; margin-right:20px; padding-right:20px; border-right:1px solid #e2e8f0;">
                    <div style="display:flex; align-items:center; background: #fee2e2; color: #991b1b; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; gap:6px;">
                        <span style="display:inline-block; width:6px; height:6px; background:#ef4444; border-radius:50%; animation: pulse 2s infinite;"></span>
                        Drive Disconnected
                    </div>
                    <a href="index.php?page=logout" style="font-size: 0.75rem; color: #b91c1c; font-weight: 700; text-decoration: underline;">Fix Now</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="topbar-user" id="userDropdownTrigger">
            <div class="topbar-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="topbar-user-info">
                <span class="topbar-user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
                <span class="topbar-user-role role-<?= $_SESSION['user_role'] ?? 'student' ?>"><?= ucfirst($_SESSION['user_role'] ?? '') ?></span>
            </div>
            <svg class="topbar-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        <!-- User dropdown -->
        <div class="topbar-dropdown" id="userDropdown">
            <div class="topbar-dropdown-header">
                <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong>
                <span><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></span>
            </div>
            <a href="index.php?page=logout" class="topbar-dropdown-item topbar-logout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sign Out
            </a>
        </div>
    </div>
</header>

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Navigation</div>
        <a href="index.php?page=announcements" class="sidebar-link <?= (($_GET['page'] ?? 'announcements') === 'announcements') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            <span>Announcements</span>
        </a>
        <a href="index.php?page=memo" class="sidebar-link <?= (($_GET['page'] ?? '') === 'memo') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>Memo</span>
        </a>
        <a href="index.php?page=upload" class="sidebar-link <?= (($_GET['page'] ?? '') === 'upload') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>Upload</span>
        </a>
        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <a href="index.php?page=visit" class="sidebar-link <?= (($_GET['page'] ?? '') === 'visit') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Visit Analytics</span>
        </a>
        <a href="index.php?page=users" class="sidebar-link <?= (($_GET['page'] ?? '') === 'users') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Accounts</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="index.php?page=logout" class="sidebar-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- ── OVERLAY (mobile) ── -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php endif; ?>

<?php if ($isGuest): ?>
<style>
    .admin-main { margin: 0 !important; width: 100% !important; min-height: 100vh !important; }
    .guest-admin-btn {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1.25rem;
        border-radius: 30px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    .guest-admin-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        color: #ffffff;
        transform: translateY(-1px);
    }
</style>
<a href="index.php?page=admin" class="guest-admin-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
    </svg>
    Admin & Staff Login
</a>
<?php endif; ?>

<!-- ── FLASH MESSAGES ── -->
<?php if (!empty($_SESSION['error'])): ?>
<div class="admin-flash admin-flash-error" id="flashMsg">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($_SESSION['error']) ?>
    <button onclick="this.parentElement.remove()">×</button>
</div>
<?php unset($_SESSION['error']); endif; ?>

<!-- ── MAIN CONTENT ── -->
<main class="admin-main" id="adminMain">
    <?= $content ?? '' ?>
</main>

<script>
// Sidebar toggle
const toggle   = document.getElementById('sidebarToggle');
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebarOverlay');
const main     = document.getElementById('adminMain');

if (toggle) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    });
}

// User dropdown
const userTrigger  = document.getElementById('userDropdownTrigger');
const userDropdown = document.getElementById('userDropdown');
if (userTrigger) {
    userTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
    document.addEventListener('click', () => userDropdown.classList.remove('show'));
}

// Auto-dismiss flash
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash && flash.remove(), 5000);

// --- Real-time Drive Polling ---
<?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
(function() {
    let lastCheck = 0;
    const interval = 30000; // 30 seconds
    const page = '<?= $_GET['page'] ?? 'home' ?>';
    
    // Only poll on document-heavy pages
    const syncablePages = ['upload', 'announcements', 'memo', 'home'];
    if (!syncablePages.includes(page)) return;

    function checkForUpdates() {
        const url = `index.php?page=${page}&action=ajaxSync`;
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.changesDetected) {
                    notifyUpdates(data.counts);
                }
            })
            .catch(err => console.error('Sync Poller Error:', err));
    }

    function notifyUpdates(counts) {
        const syncBtn = document.querySelector('.topbar-sync-btn');
        if (syncBtn) syncBtn.classList.add('sync-pending');

        const msg = [];
        if (counts.imported > 1) msg.push(`${counts.imported} new files found`);
        else if (counts.imported === 1) msg.push(`1 new file found`);
        
        if (counts.purged > 0) msg.push(`${counts.purged} files removed`);
        if (counts.uploaded > 0) msg.push(`${counts.uploaded} files synced to cloud`);

        if (msg.length > 0) {
            showToast(`Cloud Update: ${msg.join(', ')}. Refreshing dashboard...`);
            // Optional: Auto-reload after a delay or just update the table
            setTimeout(() => location.reload(), 3000);
        }
    }

    function showToast(text) {
        let toast = document.createElement('div');
        toast.className = 'sync-toast';
        toast.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            <span>${text}</span>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 6000);
    }

    setInterval(checkForUpdates, interval);
})();
<?php endif; ?>
</script>

<style>
.sync-pending {
    animation: pulse-gold 2s infinite;
    border-color: #d97706 !important;
    background: #fffbeb !important;
    color: #92400e !important;
}

@keyframes pulse-gold {
    0% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(217, 119, 6, 0); }
    100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
}

.sync-toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: #0f172a;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    z-index: 10000;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid rgba(255,255,255,0.1);
}

.sync-toast.show {
    transform: translateY(0);
    opacity: 1;
}

.sync-toast svg {
    color: #fbbf24;
    animation: rotate-sync 2s linear infinite;
}

@keyframes rotate-sync {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
</body>
</html>
