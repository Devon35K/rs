<?php
class HomeController {
    private Announcement $announcementModel;
    private Memo $memoModel;

    public function __construct() {
        $this->announcementModel = new Announcement();
        $this->memoModel = new Memo();
    }

    public function ajaxSync(): void {
        header('Content-Type: application/json');
        $drive = new \Services\GoogleDriveService();
        if (!$drive->isReady()) {
            echo json_encode(['status' => 'error', 'message' => 'Drive not authorized']);
            return;
        }

        try {
            $changes = false; $totalImported = 0; $totalPurged = 0;

            // 1. Sync Announcements
            $annModel = new \Announcement();
            $driveFiles = $drive->syncFolders('announcements');
            $ids = [];
            foreach ($driveFiles as $f) {
                $ids[] = $f['drive_file_id'];
                if (!$annModel->findByDriveId($f['drive_file_id'])) $totalImported++;
                $annModel->upsertFromDrive(['title'=>$f['title'],'body'=>'Imported','audience'=>'all','attachment_path'=>'','drive_link'=>$f['drive_link'],'drive_file_id'=>$f['drive_file_id'],'user_id'=>$_SESSION['user_id']]);
            }
            foreach ($annModel->getAllSyncedRecords() as $r) {
                if (!in_array($r['drive_file_id'], $ids)) { $annModel->delete($r['id']); $totalPurged++; }
            }

            // 2. Sync Memos
            $memoModel = new \Memo();
            $driveFiles = $drive->syncFolders('memorandums');
            $ids = [];
            foreach ($driveFiles as $f) {
                $ids[] = $f['drive_file_id'];
                if (!$memoModel->findByDriveId($f['drive_file_id'])) $totalImported++;
                $memoModel->upsertFromDrive(['memo_no'=>'AUTO-'.substr($f['drive_file_id'],0,5),'date_issued'=>date('Y-m-d'),'subject'=>$f['title'],'category'=>'General','type'=>'internal','file_path'=>'','link'=>$f['drive_link'],'drive_file_id'=>$f['drive_file_id'],'user_id'=>$_SESSION['user_id']]);
            }
            foreach ($memoModel->getAllSyncedRecords() as $r) {
                if (!in_array($r['drive_file_id'], $ids)) { $memoModel->delete($r['id']); $totalPurged++; }
            }

            $changes = ($totalImported > 0 || $totalPurged > 0);

            echo json_encode([
                'status' => 'success',
                'changesDetected' => $changes,
                'counts' => ['imported' => $totalImported, 'purged' => $totalPurged]
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
