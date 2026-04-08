<?php
class MemoController {
    private Memo $model;

    public function __construct() {
        $this->model = new Memo();
    }

    public function index(): void {
        $memos = $this->model->all();
        require BASE_PATH . '/views/memos/index.php';
    }

    public function store(): void {
        $this->requireAdminOrFaculty();

        $data = [
            'memo_no'    => trim($_POST['memo_no'] ?? ''),
            'date_issued'=> $_POST['date_issued'] ?? date('Y-m-d'),
            'subject'    => trim($_POST['subject'] ?? ''),
            'category'   => trim($_POST['category'] ?? ''),
            'type'       => $_POST['type'] ?? 'internal',
            'link'       => trim($_POST['link'] ?? ''),
            'user_id'    => $_SESSION['user_id'],
        ];

        if (empty($data['memo_no']) || empty($data['subject'])) {
            $_SESSION['error'] = 'Memo No. and Subject are required.';
            header('Location: index.php?page=memo');
            exit;
        }

        // Handle file upload
        if (!empty($_FILES['file']['name'])) {
            $uploadDir = BASE_PATH . '/public/uploads/memos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext      = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename);
            $data['file_path'] = 'uploads/memos/' . $filename;
        }

        $this->model->create($data);
        $_SESSION['success'] = 'Memo added successfully.';
        header('Location: index.php?page=memo');
        exit;
    }

    public function delete(int $id): void {
        $this->requireAdmin();
        $this->model->delete($id);
        $_SESSION['success'] = 'Memo deleted.';
        header('Location: index.php?page=memo');
        exit;
    }

    private function requireAdminOrFaculty(): void {
        if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'faculty'])) {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=memo');
            exit;
        }
    }

    private function requireAdmin(): void {
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $_SESSION['error'] = 'Unauthorized.';
            header('Location: index.php?page=memo');
            exit;
        }
    }
}