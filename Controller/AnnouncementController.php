<?php
class AnnouncementController {
    private Announcement $model;

    public function __construct() {
        $this->model = new Announcement();
    }

    public function index(): void {
        $role          = $_SESSION['user_role'] ?? 'student';
        $announcements = ($role === 'admin')
            ? $this->model->all()
            : $this->model->forAudience($role);

        require BASE_PATH . '/views/announcements/index.php';
    }

    public function store(): void {
        $this->requireAdminOrFaculty();

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $audience = $_POST['audience'] ?? 'all';

        if (empty($title) || empty($body)) {
            $_SESSION['error'] = 'Title and body are required.';
            header('Location: index.php?page=announcements');
            exit;
        }

        // Handle image upload
        $coverImage = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $coverImage = $this->uploadFile($_FILES['cover_image'], 'images');
        }

        $this->model->create([
            'title'       => $title,
            'body'        => $body,
            'audience'    => $audience,
            'cover_image' => $coverImage,
            'user_id'     => $_SESSION['user_id'],
        ]);

        $_SESSION['success'] = 'Announcement posted successfully.';
        header('Location: index.php?page=announcements');
        exit;
    }

    public function delete(int $id): void {
        $this->requireAdmin();
        $this->model->delete($id);
        $_SESSION['success'] = 'Announcement deleted.';
        header('Location: index.php?page=announcements');
        exit;
    }

    private function uploadFile(array $file, string $folder): string {
        $uploadDir = BASE_PATH . '/public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
        return 'uploads/' . $folder . '/' . $filename;
    }

    private function requireAdminOrFaculty(): void {
        if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty'])) {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=announcements');
            exit;
        }
    }

    private function requireAdmin(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=announcements');
            exit;
        }
    }
}