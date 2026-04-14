<?php
class MemoController {
    private Memo $model;
    private ?Services\GoogleDriveService $drive;

    public function __construct() {
        $this->model = new Memo();
        $this->drive = new Services\GoogleDriveService();
    }

    public function index(): void {
        // Quick Sync for Admins
        if (($_SESSION['user_role'] ?? '') === 'admin' && $this->drive->isReady()) {
            $this->sync(false);
        }
        
        $memos = $this->model->all();
        require BASE_PATH . '/views/memos/index.php';
    }
    public function sync(bool $redirect = true): void {
        if (!$this->drive->isReady()) {
            if ($redirect) {
                $_SESSION['error'] = 'Google Drive not authorized.';
                header('Location: index.php?page=memo');
                exit;
            }
            return;
        }

        try {
            // PHASE A: System -> Drive (Upload missing local memos)
            $unsynced = $this->model->getUnsynced();
            foreach ($unsynced as $item) {
                $localPath = BASE_PATH . '/public/' . $item['file_path'];
                if (file_exists($localPath)) {
                    $result = $this->drive->uploadAndSort($localPath, $item['subject'], $item['category'], 'memorandums');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('memorandums');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                // Determine a memo number from title if possible
                $memoNo = 'AUTO-' . substr($file['drive_file_id'], 0, 5);
                
                $this->model->upsertFromDrive([
                    'memo_no'     => $memoNo,
                    'date_issued' => date('Y-m-d'),
                    'subject'     => $file['title'],
                    'category'    => 'General',
                    'type'        => 'internal',
                    'file_path'   => '',
                    'link'        => $file['drive_link'],
                    'drive_file_id' => $file['drive_file_id'],
                    'user_id'     => $_SESSION['user_id']
                ]);
            }

            // PHASE C: Cleanup (Purge missing from Drive)
            $syncedRecords = $this->model->getAllSyncedRecords();
            foreach ($syncedRecords as $rec) {
                if (!in_array($rec['drive_file_id'], $activeDriveIds)) {
                    // File was deleted from Drive, so delete from system
                    if (!empty($rec['file_path']) && file_exists(BASE_PATH . '/public/' . $rec['file_path'])) {
                        unlink(BASE_PATH . '/public/' . $rec['file_path']);
                    }
                    $this->model->delete($rec['id']);
                }
            }

            if ($redirect) $_SESSION['success'] = 'Memos synchronized with Google Drive (Mirror Sync).';
        } catch (Exception $e) {
            if ($redirect) $_SESSION['error'] = 'Sync Error: ' . $e->getMessage();
        }

        if ($redirect) {
            header('Location: index.php?page=memo');
            exit;
        }
    }

    public function ajaxSync(): void {
        header('Content-Type: application/json');
        if (!$this->drive->isReady()) {
            echo json_encode(['status' => 'error', 'message' => 'Drive not authorized']);
            return;
        }

        try {
            $uploaded = 0; $imported = 0; $purged = 0;
            
            // PHASE A: System -> Drive
            $unsynced = $this->model->getUnsynced();
            foreach ($unsynced as $item) {
                $localPath = BASE_PATH . '/public/' . $item['file_path'];
                if (file_exists($localPath)) {
                    $result = $this->drive->uploadAndSort($localPath, $item['subject'], $item['category'], 'memorandums');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                        $uploaded++;
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('memorandums');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                $existing = $this->model->findByDriveId($file['drive_file_id']);
                if (!$existing) { $imported++; }
                
                // Determine a memo number from title if possible
                $memoNo = 'AUTO-' . substr($file['drive_file_id'], 0, 5);
                
                $this->model->upsertFromDrive([
                    'memo_no'     => $memoNo,
                    'date_issued' => date('Y-m-d'),
                    'subject'     => $file['title'],
                    'category'    => 'General',
                    'type'        => 'internal',
                    'file_path'   => '',
                    'link'        => $file['drive_link'],
                    'drive_file_id' => $file['drive_file_id'],
                    'user_id'     => $_SESSION['user_id']
                ]);
            }

            // PHASE C: Cleanup
            $syncedRecords = $this->model->getAllSyncedRecords();
            foreach ($syncedRecords as $rec) {
                if (!in_array($rec['drive_file_id'], $activeDriveIds)) {
                    if (!empty($rec['file_path']) && file_exists(BASE_PATH . '/public/' . $rec['file_path'])) {
                        unlink(BASE_PATH . '/public/' . $rec['file_path']);
                    }
                    $this->model->delete($rec['id']);
                    $purged++;
                }
            }

            echo json_encode([
                'status' => 'success',
                'changesDetected' => ($uploaded > 0 || $imported > 0 || $purged > 0),
                'counts' => ['uploaded' => $uploaded, 'imported' => $imported, 'purged' => $purged]
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
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

        // Handle Google Drive Upload
        if (!empty($_FILES['file']['name']) && $this->drive->isReady()) {
            try {
                $result = $this->drive->uploadAndSort($_FILES['file']['tmp_name'], $_FILES['file']['name'], 'Memo', 'memorandums');
                if ($result['id']) {
                    $data['link'] = $result['link'];
                    $data['drive_file_id'] = $result['id'];
                }
            } catch (Exception $e) {
                error_log("Memo Drive Error: " . $e->getMessage());
            }
        }

        // Handle local file upload (only if Drive setup is missing or fails)
        if (!empty($_FILES['file']['name']) && empty($data['drive_file_id'])) {
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
        $memo = $this->model->find($id);

        if ($memo) {
            // Delete from Drive if exists
            if (!empty($memo['drive_file_id']) && $this->drive->isReady()) {
                $this->drive->deleteFile($memo['drive_file_id']);
            }
            $this->model->delete($id);
        }

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