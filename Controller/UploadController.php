<?php
class UploadController {
    private Document $model;

    public function __construct() {
        $this->model = new Document();
    }

    public function index(): void {
        $role = $_SESSION['user_role'] ?? 'student';
        $documents = ($role === 'admin')
            ? $this->model->all()
            : $this->model->forAudience($role);

        require BASE_PATH . '/views/uploads/index.php';
    }

    public function store(): void {
        $this->requireAdminOrFaculty();

        $title     = trim($_POST['title'] ?? '');
        $audience  = $_POST['audience'] ?? 'all';
        $driveLink = trim($_POST['drive_link'] ?? '');
        $hasFile   = !empty($_FILES['document']['name']);

        if (!$hasFile && empty($driveLink)) {
            $_SESSION['error'] = 'Please select a file to upload or provide a Google Drive Link.';
            header('Location: index.php?page=upload');
            exit;
        }

        if (empty($title)) {
            $_SESSION['error'] = 'Document title is required.';
            header('Location: index.php?page=upload');
            exit;
        }

        $filepath = '';
        $fileType = '';
        $fileSize = 0;

        if ($hasFile) {
            $uploadDir = BASE_PATH . '/public/uploads/documents/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $file     = $_FILES['document'];
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $filepath = 'uploads/documents/' . $filename;
            $fileType = $file['type'];
            $fileSize = $file['size'];
        }

        $this->model->create([
            'title'       => $title,
            'description' => trim($_POST['description'] ?? ''),
            'file_path'   => $filepath,
            'file_type'   => $fileType,
            'file_size'   => $fileSize,
            'category'    => trim($_POST['category'] ?? ''),
            'audience'    => $audience,
            'drive_link'  => $driveLink,
            'user_id'     => $_SESSION['user_id'],
        ]);

        $_SESSION['success'] = 'Document uploaded successfully.';
        header('Location: index.php?page=upload');
        exit;
    }

    public function delete(int $id): void {
        $this->requireAdmin();
        $doc = $this->model->find($id);
        if ($doc && file_exists(BASE_PATH . '/public/' . $doc['file_path'])) {
            unlink(BASE_PATH . '/public/' . $doc['file_path']);
        }
        $this->model->delete($id);
        $_SESSION['success'] = 'Document deleted.';
        header('Location: index.php?page=upload');
        exit;
    }

    private function requireAdminOrFaculty(): void {
        if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty'])) {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=upload');
            exit;
        }
    }

    private function requireAdmin(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=upload');
            exit;
        }
    }
}