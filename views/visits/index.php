<?php ob_start(); ?>

<!-- ══════════════════════════════════════════════════
     ANALYTICS HERO
══════════════════════════════════════════════════ -->
<div class="va-hero">
    <div class="va-hero-overlay"></div>
    <div class="va-hero-content">
        <div class="va-hero-eyebrow">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Visit Analytics
        </div>
        <h1 class="va-hero-title">Portal Dashboard</h1>
        <p class="va-hero-sub">Real-time overview of user activity, content, and system engagement.</p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     KPI STAT CARDS
══════════════════════════════════════════════════ -->
<section class="va-section">
    <div class="va-kpi-grid">

        <!-- Total Visits -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $totalVisits ?? 0 ?>"><?= $totalVisits ?? 0 ?></div>
                <div class="va-kpi-label">Total Visits</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--blue">All time</div>
        </div>

        <!-- Today's Visits -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $todayVisits ?? 0 ?>"><?= $todayVisits ?? 0 ?></div>
                <div class="va-kpi-label">Today's Visits</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--green">Today</div>
        </div>

        <!-- Total Users -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $totalUsers ?? 0 ?>"><?= $totalUsers ?? 0 ?></div>
                <div class="va-kpi-label">Registered Users</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--purple">Accounts</div>
        </div>

        <!-- Announcements -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $totalAnnouncements ?? 0 ?>"><?= $totalAnnouncements ?? 0 ?></div>
                <div class="va-kpi-label">Announcements</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--red">Published</div>
        </div>

        <!-- Memos -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $totalMemos ?? 0 ?>"><?= $totalMemos ?? 0 ?></div>
                <div class="va-kpi-label">Memos</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--orange">Issued</div>
        </div>

        <!-- Documents -->
        <div class="va-kpi-card">
            <div class="va-kpi-icon va-kpi-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div class="va-kpi-body">
                <div class="va-kpi-value" data-count="<?= $totalDocs ?? 0 ?>"><?= $totalDocs ?? 0 ?></div>
                <div class="va-kpi-label">Documents</div>
            </div>
            <div class="va-kpi-badge va-kpi-badge--teal">Uploaded</div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════════
     TWO-COLUMN: USER BREAKDOWN + RECENT VISITS
══════════════════════════════════════════════════ -->
<section class="va-section va-section--gray">
    <div class="va-two-col">

        <!-- User Breakdown card -->
        <div class="va-panel">
            <div class="va-panel-header">
                <div class="va-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    User Breakdown
                </div>
                <span class="va-panel-sub"><?= $totalUsers ?? 0 ?> total</span>
            </div>
            <div class="va-panel-body">

                <?php
                $roles = [
                    ['label' => 'Admin',   'count' => $userModel->countByRole('admin'),   'color' => 'red'],
                    ['label' => 'Faculty', 'count' => $totalFaculty ?? 0,                 'color' => 'purple'],
                    ['label' => 'Student', 'count' => $totalStudents ?? 0,                'color' => 'blue'],
                ];
                foreach ($roles as $r):
                    $pct = ($totalUsers > 0) ? round(($r['count'] / $totalUsers) * 100, 1) : 0;
                ?>
                <div class="va-breakdown-row">
                    <div class="va-breakdown-top">
                        <span class="va-breakdown-label"><?= $r['label'] ?></span>
                        <span class="va-breakdown-count"><?= $r['count'] ?> <small>(<?= $pct ?>%)</small></span>
                    </div>
                    <div class="va-progress-track">
                        <div class="va-progress-bar va-progress-bar--<?= $r['color'] ?>" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- User list -->
                <div class="va-user-list">
                    <?php foreach (array_slice($users ?? [], 0, 6) as $u): ?>
                    <div class="va-user-row">
                        <div class="va-user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                        <div class="va-user-info">
                            <span class="va-user-name"><?= htmlspecialchars($u['name']) ?></span>
                            <span class="va-user-email"><?= htmlspecialchars($u['email']) ?></span>
                        </div>
                        <span class="va-role-badge va-role-badge--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <!-- Recent Visits card -->
        <div class="va-panel">
            <div class="va-panel-header">
                <div class="va-panel-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Recent Visits
                </div>
                <span class="va-panel-sub">Last 20 logins</span>
            </div>
            <div class="va-panel-body va-panel-body--flush">

                <?php if (empty($recentVisits)): ?>
                <div class="va-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    <p>No visits recorded yet.</p>
                </div>
                <?php else: ?>
                <div class="va-visits-table-wrap">
                    <table class="va-visits-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Page</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentVisits as $visit): ?>
                            <tr>
                                <td>
                                    <div class="va-visit-user">
                                        <div class="va-visit-avatar"><?= strtoupper(substr($visit['name'] ?? 'G', 0, 1)) ?></div>
                                        <div>
                                            <div class="va-visit-name"><?= htmlspecialchars($visit['name'] ?? 'Guest') ?></div>
                                            <div class="va-visit-ip"><?= htmlspecialchars($visit['ip_address'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="va-role-badge va-role-badge--<?= $visit['role'] ?? 'guest' ?>">
                                        <?= ucfirst($visit['role'] ?? 'Guest') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="va-page-chip"><?= htmlspecialchars($visit['page'] ?? 'home') ?></span>
                                </td>
                                <td class="va-visit-time"><?= date('M d, g:i a', strtotime($visit['visited_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</section>

<script>
/* Animate KPI counters on load */
document.querySelectorAll('.va-kpi-value[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    if (!target) return;
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 40));
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString();
        if (current >= target) clearInterval(timer);
    }, 30);
});

/* Animate progress bars */
window.addEventListener('load', () => {
    document.querySelectorAll('.va-progress-bar').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0';
        requestAnimationFrame(() => {
            bar.style.transition = 'width 0.9s cubic-bezier(0.4,0,0.2,1)';
            bar.style.width = w;
        });
    });
});
</script>

<?php
$content   = ob_get_clean();
$pageTitle = 'Analytics — BSIT Department';
require BASE_PATH . '/views/layouts/main.php';
