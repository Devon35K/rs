<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Staff Login — BSIT Department</title>
    <meta name="description" content="Sign in to BSIT Department — the academic staff management platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=DM+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>

<body class="login-body">



    <!-- ── FULL-SCREEN BACKGROUND ── -->
    <div class="login-bg" style="background-image: url('<?= BASE_URL ?>icon/background.png');"></div>
    <div class="login-scene-overlay"></div>

    <!-- ── LOGIN CARD ── -->
    <main class="login-stage">

        <!-- Branding above card -->
        <div class="login-brand">
            <div class="login-brand-seal">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="24" cy="24" r="22" stroke="white" stroke-width="2" opacity="0.6" />
                    <path d="M24 10 L38 18 L38 30 L24 38 L10 30 L10 18 Z" stroke="white" stroke-width="1.5"
                        fill="rgba(255,255,255,0.08)" />
                    <path d="M16 22 L24 14 L32 22 L32 34 L16 34 Z" fill="rgba(255,255,255,0.15)" stroke="white"
                        stroke-width="1.2" />
                    <rect x="21" y="26" width="6" height="8" fill="white" opacity="0.85" />
                    <path d="M12 24 L24 16 L36 24" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </div>
            <div class="login-brand-text">
                <span class="login-brand-name">BSIT Department</span>
                <span class="login-brand-sub">Academic Staff Portal</span>
            </div>
        </div>

        <article class="login-card" role="main">

            <!-- Card header bar -->
            <div class="login-card-bar">
                <div class="login-card-bar-left">
                    <div class="login-card-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18"
                            height="18">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="login-card-title">Admin & Staff Login</h1>
                        <p class="login-card-sub">Authorized personnel only</p>
                    </div>
                </div>
            </div>

            <!-- Card body -->
            <div class="login-card-body">
                <p class="login-greeting">Welcome back! Please sign in to continue.</p>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="login-alert-error">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); endif; ?>

                <form method="POST" action="index.php?page=admin" class="login-form" id="loginForm" novalidate>

                    <!-- Email -->
                    <div class="lf-group">
                        <label class="lf-label" for="email">Employee / Staff Email</label>
                        <div class="lf-input-wrap">
                            <svg class="lf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            <input type="email" id="email" name="email" class="lf-input"
                                placeholder="yourname@usep.edu.ph" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required autocomplete="email" pattern=".+@usep\.edu\.ph"
                                title="Only @usep.edu.ph email addresses are accepted">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="lf-group">
                        <div class="lf-label-row">
                            <label class="lf-label" for="password">Password</label>
                        </div>
                        <div class="lf-input-wrap">
                            <svg class="lf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <input type="password" id="password" name="password" class="lf-input" placeholder="••••••••"
                                required autocomplete="current-password">
                            <button type="button" class="lf-toggle-pw" aria-label="Show/hide password"
                                onclick="togglePassword()">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="lf-divider"></div>

                    <button type="submit" class="lf-btn-submit" id="submitBtn">
                        <svg class="lf-btn-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <span class="lf-btn-text">SIGN IN</span>
                        <svg class="lf-btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12,5 19,12 12,19" />
                        </svg>
                    </button>

                </form>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="index.php"
                        style="color: #64748b; text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.4rem; transition: color 0.2s;"
                        onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Return to Guest Dashboard
                    </a>
                </div>

            </div>
            <!-- End card body -->

            <!-- Card footer -->
            <div class="login-card-footer">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                Secured connection &nbsp;·&nbsp; BSIT Department © <?= date('Y') ?>
            </div>

        </article>
    </main>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                pw.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }

        // Ripple effect on button
        document.getElementById('submitBtn').addEventListener('click', function (e) {
            const btn = this;
            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            ripple.className = 'lf-ripple';
            ripple.style.left = (e.clientX - rect.left) + 'px';
            ripple.style.top = (e.clientY - rect.top) + 'px';
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    </script>
</body>

</html>