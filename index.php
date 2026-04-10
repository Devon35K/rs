<?php
session_start();
define('BASE_PATH', __DIR__);
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $scriptDir === '' ? '/' : $scriptDir . '/');

// Load environment variables from .env file (for local development)
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

require_once BASE_PATH . '/database.php';
require_once BASE_PATH . '/User.php';
require_once BASE_PATH . '/Announcement.php';
require_once BASE_PATH . '/Memo.php';
require_once BASE_PATH . '/Document.php';
require_once BASE_PATH . '/AuthController.php';
require_once BASE_PATH . '/AnnouncementController.php';
require_once BASE_PATH . '/MemoController.php';
require_once BASE_PATH . '/UploadController.php';
require_once BASE_PATH . '/VisitController.php';
require_once BASE_PATH . '/UserController.php';
require_once BASE_PATH . '/HomeController.php';

$request = $_GET['page'] ?? 'home';
$action  = $_GET['action'] ?? 'index';

// Simple router
switch ($request) {
    case 'admin':
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->login();
        } else {
            $ctrl->showLogin();
        }
        break;
    case 'logout':
        $ctrl = new AuthController();
        $ctrl->logout();
        break;
    case 'google-login':
        $ctrl = new AuthController();
        $ctrl->googleRedirect();
        break;
    case 'google-callback':
        $ctrl = new AuthController();
        $ctrl->googleCallback();
        break;
    case 'announcements':
        requireAuth();
        $ctrl = new AnnouncementController();
        if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } elseif ($action === 'delete') {
            $ctrl->delete($_GET['id'] ?? 0);
        } else {
            $ctrl->index();
        }
        break;
    case 'memo':
        requireAuth();
        $ctrl = new MemoController();
        if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } elseif ($action === 'delete') {
            $ctrl->delete($_GET['id'] ?? 0);
        } else {
            $ctrl->index();
        }
        break;
    case 'upload':
        requireAuth();
        $ctrl = new UploadController();
        if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } elseif ($action === 'delete') {
            $ctrl->delete($_GET['id'] ?? 0);
        } else {
            $ctrl->index();
        }
        break;
    case 'visit':
        requireAuth();
        $ctrl = new VisitController();
        $ctrl->index();
        break;
    case 'users':
        requireAuth();
        $ctrl = new UserController();
        if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } elseif ($action === 'delete') {
            $ctrl->delete($_GET['id'] ?? 0);
        } else {
            $ctrl->index();
        }
        break;
    default:
    case 'home':
        $ctrl = new HomeController();
        $ctrl->index();
        break;
}

function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php?page=admin');
        exit;
    }
}