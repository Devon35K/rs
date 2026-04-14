<?php
require_once BASE_PATH . '/config/google_config.php';
class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin(): void {
        if (!empty($_SESSION['user_id'])) {
            header('Location: index.php?page=announcements');
            exit;
        }
        require BASE_PATH . '/views/auth/login.php';
    }

    public function login(): void {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Enforce @usep.edu.ph domain
        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));
        if ($emailDomain !== 'usep.edu.ph') {
            $_SESSION['error'] = 'Only @usep.edu.ph email addresses are allowed.';
            header('Location: index.php?page=admin');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Invalid email or password.';
            header('Location: index.php?page=admin');
            exit;
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['success']   = 'Welcome back, ' . $user['name'] . '!';

        // Log visit
        $this->logVisit($user['id']);

        header('Location: index.php?page=announcements');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php?page=admin');
        exit;
    }

    /* ── Google OAuth 2.0 ── */

    /**
     * Step 1 — Redirect the user to Google's OAuth consent screen.
     * The `hd` parameter restricts the account chooser to usep.edu.ph.
     */
    public function googleRedirect(): void {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive',
            'hd'            => GOOGLE_ALLOWED_DOMAIN,   // restrict to usep.edu.ph
            'state'         => $state,
            'prompt'        => 'consent',               // force consent to ensure we get a refresh token
            'access_type'   => 'offline',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;
    }

    /**
     * Step 2 — Google redirects back here with ?code=...&state=...
     * Exchange the code for tokens, verify the domain, then log in.
     */
    public function googleCallback(): void {
        // CSRF state check
        $state = $_GET['state'] ?? '';
        if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
            $_SESSION['error'] = 'Invalid OAuth state. Please try again.';
            header('Location: index.php?page=admin');
            exit;
        }
        unset($_SESSION['oauth_state']);

        // Error from Google?
        if (!empty($_GET['error'])) {
            $_SESSION['error'] = 'Google sign-in was cancelled or failed.';
            header('Location: index.php?page=admin');
            exit;
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $_SESSION['error'] = 'No authorization code received from Google.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Exchange code → access token
        $tokenData = $this->googleExchangeCode($code);
        if (empty($tokenData['access_token'])) {
            $_SESSION['error'] = 'Failed to retrieve access token from Google.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Fetch user profile
        $profile = $this->googleFetchProfile($tokenData['access_token']);
        if (empty($profile['email'])) {
            $_SESSION['error'] = 'Could not retrieve your Google account information.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Enforce @usep.edu.ph domain
        $emailDomain = strtolower(substr(strrchr($profile['email'], '@'), 1));
        if ($emailDomain !== strtolower(GOOGLE_ALLOWED_DOMAIN)) {
            $_SESSION['error'] = 'Only @' . GOOGLE_ALLOWED_DOMAIN . ' accounts are allowed.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Find or create the user record
        $user = $this->userModel->findOrCreateByGoogle([
            'google_id'     => $profile['sub']  ?? $profile['id'] ?? '',
            'name'          => $profile['name'] ?? $profile['email'],
            'email'         => $profile['email'],
            'avatar'        => $profile['picture'] ?? null,
            'refresh_token' => $tokenData['refresh_token'] ?? null,
        ]);

        // If user already existed but we got a NEW refresh token, update it
        if ($user && !empty($tokenData['refresh_token'])) {
            $this->userModel->updateRefreshToken($user['id'], $tokenData['refresh_token']);
        }

        if (!$user) {
            $_SESSION['error'] = 'Could not sign you in. Please contact support.';
            header('Location: index.php?page=admin');
            exit;
        }

        // Create session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['success']   = 'Welcome, ' . $user['name'] . '!';

        $this->logVisit($user['id']);

        header('Location: index.php?page=announcements');
        exit;
    }

    /** Exchange authorization code for token via native streams (cURL fallback) */
    private function googleExchangeCode(string $code): array {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true) ?: [];
    }

    /** Fetch the signed-in user's profile from Google via native streams */
    private function googleFetchProfile(string $accessToken): array {
        $url = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $options = [
            'http' => [
                'header' => "Authorization: Bearer " . $accessToken . "\r\n",
                'method' => 'GET',
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true) ?: [];
    }

    private function logVisit(int $userId): void {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO visit_logs (user_id, ip_address, page, user_agent) VALUES (?, ?, 'login', ?)"
            );
            $stmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
        } catch (Exception $e) { /* silent */ }
    }
}