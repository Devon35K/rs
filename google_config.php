<?php
/**
 * Google OAuth 2.0 Configuration
 * ─────────────────────────────────────────────────────────────
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a project → APIs & Services → Credentials
 * 3. Create an OAuth 2.0 Client ID (Web Application)
 * 4. Add Authorized Redirect URI:
 *       http://localhost/rs/index.php?page=google-callback
 * 5. Fill in GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET below.
 * ─────────────────────────────────────────────────────────────
 */

define('GOOGLE_CLIENT_ID',     getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI',  getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/rs/index.php?page=google-callback');

/**
 * Only allow logins from this hosted domain (Google Workspace).
 * Change this if your institution uses a different domain.
 */
define('GOOGLE_ALLOWED_DOMAIN', getenv('GOOGLE_ALLOWED_DOMAIN') ?: 'usep.edu.ph');
