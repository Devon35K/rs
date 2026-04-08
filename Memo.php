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
            "INSERT INTO memos (memo_no, date_issued, subject, category, type, file_path, link, user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['memo_no'], $data['date_issued'], $data['subject'],
            $data['category'], $data['type'], $data['file_path'] ?? null,
            $data['link'] ?? null, $data['user_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM memos WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM memos")->fetchColumn();
    }
}