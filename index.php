<?php
session_start();
define('BASE_PATH', __DIR__);
define('BASE_URL', '/rs/');

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