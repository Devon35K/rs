<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'BSIT Department') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>style.css">
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
</script>
</body>
</html>
