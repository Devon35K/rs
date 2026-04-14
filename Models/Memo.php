<?php
class Memo {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(): array {
        return $this->db->query(
            "SELECT m.*, u.name as author FROM memos m 
             JOIN users u ON m.user_id = u.id 
             ORDER BY m.date_issued DESC"
        )->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM memos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO memos (memo_no, date_issued, subject, category, type, file_path, link, drive_file_id, user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['memo_no'], $data['date_issued'], $data['subject'],
            $data['category'], $data['type'], $data['file_path'] ?? null,
            $data['link'] ?? null, $data['drive_file_id'] ?? null, $data['user_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByDriveId(string $driveId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM memos WHERE drive_file_id = ?");
        $stmt->execute([$driveId]);
        return $stmt->fetch();
    }

    public function upsertFromDrive(array $data): void {
        $existing = $this->findByDriveId($data['drive_file_id']);
        if (!$existing) {
            $this->create($data);
        } else {
            $stmt = $this->db->prepare("UPDATE memos SET link = ?, subject = ? WHERE id = ?");
            $stmt->execute([$data['link'], $data['subject'], $existing['id']]);
        }
    }

    public function getUnsynced(): array {
        return $this->db->query("SELECT * FROM memos WHERE drive_file_id IS NULL AND file_path != '' AND file_path IS NOT NULL")->fetchAll();
    }

    public function updateDriveInfo(int $id, string $driveId, string $driveLink): void {
        $stmt = $this->db->prepare("UPDATE memos SET drive_file_id = ?, link = ? WHERE id = ?");
        $stmt->execute([$driveId, $driveLink, $id]);
    }

    public function getAllSyncedRecords(): array {
        return $this->db->query("SELECT id, drive_file_id, file_path FROM memos WHERE drive_file_id IS NOT NULL")->fetchAll();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM memos WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM memos")->fetchColumn();
    }
}