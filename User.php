<?php

require_once __DIR__ . '/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
       <?php
class User {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$data['name'], $data['email'], $data['password'], $data['role']]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array {
        return $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    }

    public function countByRole(string $role): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    }
} return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'student'
        ]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['role'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
