<?php
class Announcement
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function all(): array
    {
        return $this->db->query(
            "SELECT a.*, u.name as author FROM announcements a 
             JOIN users u ON a.user_id = u.id 
             ORDER BY a.created_at DESC"
        )->fetchAll();
    }

    public function forAudience(string $role): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, u.name as author FROM announcements a 
             JOIN users u ON a.user_id = u.id 
             WHERE a.audience = 'all' OR a.audience = ?
             ORDER BY a.created_at DESC"
        );
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO announcements (title, body, audience, cover_image, user_id) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$data['title'], $data['body'], $data['audience'], $data['cover_image'] ?? null, $data['user_id']]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
    }
}