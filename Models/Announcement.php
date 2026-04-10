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
        $stmt = $this->db->prepare("INSERT INTO announcements (title, body, audience, cover_image, user_id) 
                                    VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['title'],
            $data['body'],
            $data['audience'] ?? 'all',
            $data['cover_image'] ?? null,
            $data['user_id']
        ]);
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
