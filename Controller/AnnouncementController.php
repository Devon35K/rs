<?php
class AnnouncementController {
    private Announcement $model;
    private ?Services\GoogleDriveService $drive;

    public function __construct() {
        $this->model = new Announcement();
        $this->drive = new Services\GoogleDriveService();
    }

    public function index(): void {
        $role          = $_SESSION['user_role'] ?? 'student';
        
        // Quick Sync for Admins
        if ($role === 'admin' && $this->drive->isReady()) {
            $this->sync(false);
        }

        $announcements = ($role === 'admin')
            ? $this->model->all()
            : $this->model->forAudience($role);

        require BASE_PATH . '/views/announcements/index.php';
    }

    public function sync(bool $redirect = true): void {
        if (!$this->drive->isReady()) {
            if ($redirect) {
                $_SESSION['error'] = 'Google Drive not authorized.';
                header('Location: index.php?page=announcements');
                exit;
            }
            return;
        }

        try {
            // PHASE A: System -> Drive (Upload missing local attachments)
            $unsynced = $this->model->getUnsynced();
            foreach ($unsynced as $item) {
                $localPath = BASE_PATH . '/public/' . $item['attachment_path'];
                if (file_exists($localPath)) {
                    $result = $this->drive->uploadAndSort($localPath, $item['title'], 'Announcement', 'announcements');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('announcements');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                $this->model->upsertFromDrive([
                    'title'         => $file['title'],
                    'body'          => 'Imported from Google Drive',
                    'audience'      => 'all',
                    'cover_image'   => null,
                    'attachment_path'=> '',
                    'drive_link'    => $file['drive_link'],
                    'drive_file_id' => $file['drive_file_id'],
                    'user_id'       => $_SESSION['user_id']
                ]);
            }

            // PHASE C: Cleanup (Purge missing from Drive)
            $syncedRecords = $this->model->getAllSyncedRecords();
            foreach ($syncedRecords as $rec) {
                if (!in_array($rec['drive_file_id'], $activeDriveIds)) {
                    // File was deleted from Drive, so delete from system
                    if (!empty($rec['attachment_path']) && file_exists(BASE_PATH . '/public/' . $rec['attachment_path'])) {
                        unlink(BASE_PATH . '/public/' . $rec['attachment_path']);
                    }
                    $this->model->delete($rec['id']);
                }
            }

            if ($redirect) $_SESSION['success'] = 'Announcements synchronized with Google Drive (Mirror Sync).';
        } catch (Exception $e) {
            if ($redirect) $_SESSION['error'] = 'Sync Error: ' . $e->getMessage();
        }

        if ($redirect) {
            header('Location: index.php?page=announcements');
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
                $localPath = BASE_PATH . '/public/' . $item['attachment_path'];
                if (file_exists($localPath)) {
                    $result = $this->drive->uploadAndSort($localPath, $item['title'], 'Announcement', 'announcements');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                        $uploaded++;
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('announcements');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                $existing = $this->model->findByDriveId($file['drive_file_id']);
                if (!$existing) { $imported++; }
                $this->model->upsertFromDrive([
                    'title'         => $file['title'],
                    'body'          => 'Imported from Google Drive',
                    'audience'      => 'all',
                    'cover_image'   => null,
                    'attachment_path'=> '',
                    'drive_link'    => $file['drive_link'],
                    'drive_file_id' => $file['drive_file_id'],
                    'user_id'       => $_SESSION['user_id']
                ]);
            }

            // PHASE C: Cleanup
            $syncedRecords = $this->model->getAllSyncedRecords();
            foreach ($syncedRecords as $rec) {
                if (!in_array($rec['drive_file_id'], $activeDriveIds)) {
                    if (!empty($rec['attachment_path']) && file_exists(BASE_PATH . '/public/' . $rec['attachment_path'])) {
                        unlink(BASE_PATH . '/public/' . $rec['attachment_path']);
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

        // Handle Drive/Document Attachment
        $driveLink = null;
        $driveFileId = null;
        if (!empty($_FILES['attachment']['name']) && $this->drive->isReady()) {
            $result = $this->drive->uploadAndSort($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name'], 'Announcement', 'announcements');
            if ($result['id']) {
                $driveLink = $result['link'];
                $driveFileId = $result['id'];
            }
        }

        $this->model->create([
            'title'         => $title,
            'body'          => $body,
            'audience'      => $audience,
            'cover_image'   => $coverImage,
            'drive_link'    => $driveLink,
            'drive_file_id' => $driveFileId,
            'user_id'       => $_SESSION['user_id'],
        ]);

        $_SESSION['success'] = 'Announcement posted successfully.';
        header('Location: index.php?page=announcements');
        exit;
    }

    public function delete(int $id): void {
        $this->requireAdmin();
        $ann = $this->model->find($id);
        
        if ($ann) {
            // Delete from Drive if exists
            if (!empty($ann['drive_file_id']) && $this->drive->isReady()) {
                $this->drive->deleteFile($ann['drive_file_id']);
            }
            $this->model->delete($id);
        }

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