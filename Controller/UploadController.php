<?php
use Services\GoogleDriveService;

use Services\MailService;

class UploadController {
    private Document $model;
    private ?GoogleDriveService $drive;

    public function __construct() {
        $this->model = new Document();
        $this->drive = new GoogleDriveService();
    }

    public function index(): void {
        $role = $_SESSION['user_role'] ?? 'student';
        
        // Quick Sync for Admins to keep library fresh
        if ($role === 'admin' && $this->drive->isReady()) {
            $this->sync(false); // background sync
        }

        $documents = ($role === 'admin')
            ? $this->model->all()
            : $this->model->forAudience($role);

        require BASE_PATH . '/views/uploads/index.php';
    }

    public function sync(bool $redirect = true): void {
        if (!$this->drive->isReady()) {
            if ($redirect) {
                $_SESSION['error'] = 'Google Drive not authorized.';
                header('Location: index.php?page=upload');
                exit;
            }
            return;
        }

        try {
            // PHASE A: System -> Drive (Upload missing local files)
            $unsynced = $this->model->getUnsynced();
            foreach ($unsynced as $item) {
                $localPath = BASE_PATH . '/public/' . $item['file_path'];
                if (file_exists($localPath)) {
                    $result = $this->drive->uploadAndSort($localPath, $item['title'], $item['category'], 'repository');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('repository');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                $this->model->upsertFromDrive([
                    'title'         => $file['title'],
                    'description'   => 'Imported from Google Drive',
                    'file_path'     => '',
                    'file_type'     => $file['file_type'],
                    'file_size'     => (int)$file['file_size'],
                    'category'      => $file['category'],
                    'audience'      => 'all',
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
                    if (!empty($rec['file_path']) && file_exists(BASE_PATH . '/public/' . $rec['file_path'])) {
                        unlink(BASE_PATH . '/public/' . $rec['file_path']);
                    }
                    $this->model->delete($rec['id']);
                }
            }

            if ($redirect) $_SESSION['success'] = 'Synchronization with Google Drive complete (Mirror Sync).';
        } catch (Exception $e) {
            if ($redirect) $_SESSION['error'] = 'Sync Error: ' . $e->getMessage();
        }

        if ($redirect) {
            header('Location: index.php?page=upload');
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
                    $result = $this->drive->uploadAndSort($localPath, $item['title'], $item['category'], 'repository');
                    if ($result['id']) {
                        $this->model->updateDriveInfo($item['id'], $result['id'], $result['link']);
                        $uploaded++;
                    }
                }
            }

            // PHASE B: Drive -> System
            $driveFiles = $this->drive->syncFolders('repository');
            $activeDriveIds = [];
            foreach ($driveFiles as $file) {
                $activeDriveIds[] = $file['drive_file_id'];
                $existing = $this->model->findByDriveId($file['drive_file_id']);
                if (!$existing) { $imported++; }
                $this->model->upsertFromDrive([
                    'title'         => $file['title'],
                    'description'   => 'Imported from Google Drive',
                    'file_path'     => '',
                    'file_type'     => $file['file_type'],
                    'file_size'     => (int)$file['file_size'],
                    'category'      => $file['category'],
                    'audience'      => 'all',
                    'drive_link'    => $file['drive_link'],
                    'drive_file_id' => $file['drive_file_id'],
                    'user_id'       => $_SESSION['user_id']
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
        $category = trim($_POST['category'] ?? 'Department');

        if ($hasFile) {
            $file = $_FILES['document'];
            $isDriveReady = $this->drive->isReady();
            
            if ($isDriveReady) {
                try {
                    // Upload to Google Drive and automatically sort into category folder
                    $result = $this->drive->uploadAndSort($file['tmp_name'], $file['name'], $category, 'repository');
                    $driveLink = $result['link'];
                    $driveFileId = $result['id'];
                    $fileType  = $file['type'];
                    $fileSize  = $file['size'];
                } catch (Exception $e) {
                    error_log('Google Drive Error: ' . $e->getMessage());
                    $isDriveReady = false; // Trigger fallback
                }
            }

            // Fallback to local storage if Drive is not ready or failed
            if (!$isDriveReady) {
                $uploadDir = BASE_PATH . '/public/uploads/documents/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
                $filepath = 'uploads/documents/' . $filename;
                $fileType = $file['type'];
                $fileSize = $file['size'];
            }
        }

        $this->model->create([
            'title'         => $title,
            'description'   => trim($_POST['description'] ?? ''),
            'file_path'     => $filepath,
            'file_type'     => $fileType,
            'file_size'     => $fileSize,
            'category'      => $category,
            'audience'      => $audience,
            'drive_link'    => $driveLink,
            'drive_file_id' => $driveFileId ?? null,
            'user_id'       => $_SESSION['user_id'],
        ]);

        // Send Email Notification (Standard success report, no attachment needed anymore)
        if ($hasFile && $this->drive->isReady()) {
            $mailUser = getenv('MAIL_USER');
            if ($mailUser) {
                $subject = "File Uploaded to Drive: $title";
                $body = "<h3>File Automatically Saved to Google Drive</h3>
                         <p><strong>Title:</strong> $title</p>
                         <p><strong>Category:</strong> $category</p>
                         <p><strong>Drive Link:</strong> <a href='$driveLink'>Open in Google Drive</a></p>";
                MailService::sendNotification($mailUser, $subject, $body);
            }
        }

        $_SESSION['success'] = $hasFile && !$this->drive->isReady() 
            ? 'Document uploaded successfully to local storage (Google Drive authorization pending).' 
            : 'Document uploaded and automatically sorted in Google Drive successfully.';
        header('Location: index.php?page=upload');
        exit;
    }

    public function delete(int $id): void {
        $this->requireAdmin();
        $doc = $this->model->find($id);

        if ($doc) {
            // 1. Delete Local File if exists
            if (!empty($doc['file_path']) && file_exists(BASE_PATH . '/public/' . $doc['file_path'])) {
                unlink(BASE_PATH . '/public/' . $doc['file_path']);
            }

            // 2. Delete from Google Drive if exists and service is ready
            if (!empty($doc['drive_file_id']) && $this->drive->isReady()) {
                $this->drive->deleteFile($doc['drive_file_id']);
            }

            // 3. Delete from Database
            $this->model->delete($id);
            $_SESSION['success'] = 'Document deleted successfully from both system and Google Drive.';
        }

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