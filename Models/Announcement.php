<?php

require_once __DIR__ . '/../config/database.php';

class Announcement {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /** Fetch ALL announcements (admin view) */
    public function all(): array {
        return $this->db->query(
            "SELECT a.*, u.name as author_name FROM announcements a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC"
        )->fetchAll();
    }

    /** Fetch announcements visible to a given role (faculty/student) */
    public function forAudience(string $role): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name as author_name FROM announcements a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.audience = 'all' OR a.audience = ?
             ORDER BY a.created_at DESC"
        );
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /** Legacy alias — kept for backward compatibility */
    public function getAll(string $audience = 'all'): array {
        return $this->forAudience($audience);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT a.*, u.name as author_name FROM announcements a 
                                    LEFT JOIN users u ON a.user_id = u.id 
                                    WHERE a.id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO announcements (title, body, audience, cover_image, attachment_path, drive_link, drive_file_id, user_id) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['title'],
            $data['body'],
            $data['audience'] ?? 'all',
            $data['cover_image'] ?? null,
            $data['attachment_path'] ?? null,
            $data['drive_link'] ?? null,
            $data['drive_file_id'] ?? null,
            $data['user_id']
        ]);
    }

    public function findByDriveId(string $driveId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE drive_file_id = ?");
        $stmt->execute([$driveId]);
        return $stmt->fetch();
    }

    public function upsertFromDrive(array $data): void {
        $existing = $this->findByDriveId($data['drive_file_id']);
        if (!$existing) {
            $this->create($data);
        } else {
            $stmt = $this->db->prepare("UPDATE announcements SET drive_link = ?, title = ? WHERE id = ?");
            $stmt->execute([$data['drive_link'], $data['title'], $existing['id']]);
        }
    }

    public function getUnsynced(): array {
        return $this->db->query("SELECT * FROM announcements WHERE drive_file_id IS NULL AND attachment_path IS NOT NULL AND attachment_path != ''")->fetchAll();
    }

    public function updateDriveInfo(int $id, string $driveId, string $driveLink): void {
        $stmt = $this->db->prepare("UPDATE announcements SET drive_file_id = ?, drive_link = ? WHERE id = ?");
        $stmt->execute([$driveId, $driveLink, $id]);
    }

    public function getAllSyncedRecords(): array {
        return $this->db->query("SELECT id, drive_file_id, attachment_path FROM announcements WHERE drive_file_id IS NOT NULL")->fetchAll();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE announcements SET title = ?, body = ?, audience = ?, cover_image = ? WHERE id = ?");
        return $stmt->execute([
            $data['title'],
            $data['body'],
            $data['audience'],
            $data['cover_image'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getLatest(int $limit = 3): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name as author_name FROM announcements a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
    }

    public function find(int $id): array|false {
        return $this->findById($id);
    }
}
