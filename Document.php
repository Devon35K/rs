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
            "INSERT INTO documents (title, description, file_path, file_type, file_size, category, audience, drive_link, user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['title'], $data['description'] ?? null, $data['file_path'],
            $data['file_type'] ?? null, $data['file_size'] ?? null,
            $data['category'] ?? null, $data['audience'], $data['drive_link'] ?? null,
            $data['user_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    }
}