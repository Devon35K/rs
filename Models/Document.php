<?php
class Document {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(): array {
        return $this->db->query(
            "SELECT d.*, u.name as author FROM documents d 
             JOIN users u ON d.user_id = u.id 
             ORDER BY d.created_at DESC"
        )->fetchAll();
    }

    public function forAudience(string $role): array {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.name as author FROM documents d 
             JOIN users u ON d.user_id = u.id 
             WHERE d.audience = 'all' OR d.audience = ?
             ORDER BY d.created_at DESC"
        );
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO documents (title, description, file_path, file_type, file_size, category, audience, drive_link, drive_file_id, user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['title'], $data['description'] ?? null, $data['file_path'] ?? '',
            $data['file_type'] ?? null, $data['file_size'] ?? null,
            $data['category'] ?? null, $data['audience'] ?? 'all', $data['drive_link'] ?? null,
            $data['drive_file_id'] ?? null,
            $data['user_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByDriveId(string $driveId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM documents WHERE drive_file_id = ?");
        $stmt->execute([$driveId]);
        return $stmt->fetch();
    }

    public function upsertFromDrive(array $data): void {
        $existing = $this->findByDriveId($data['drive_file_id']);
        if (!$existing) {
            $this->create($data);
        } else {
            // Update link and category if changed
            $stmt = $this->db->prepare("UPDATE documents SET drive_link = ?, title = ?, category = ? WHERE id = ?");
            $stmt->execute([$data['drive_link'], $data['title'], $data['category'], $existing['id']]);
        }
    }

    public function getUnsynced(): array {
        return $this->db->query("SELECT * FROM documents WHERE drive_file_id IS NULL AND file_path != ''")->fetchAll();
    }

    public function updateDriveInfo(int $id, string $driveId, string $driveLink): void {
        $stmt = $this->db->prepare("UPDATE documents SET drive_file_id = ?, drive_link = ? WHERE id = ?");
        $stmt->execute([$driveId, $driveLink, $id]);
    }

    public function getAllSyncedRecords(): array {
        return $this->db->query("SELECT id, drive_file_id, file_path FROM documents WHERE drive_file_id IS NOT NULL")->fetchAll();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    }
}