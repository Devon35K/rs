<?php

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
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

    /** Alias used by VisitController */
    public function all(): array {
        return $this->getAll();
    }

    public function countByRole(string $role): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
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

    /**
     * Find or create a user from Google OAuth data.
     * Matches first by google_id, then by email.
     * If neither exists, creates a new account (password = NULL).
     */
    public function findOrCreateByGoogle(array $googleUser): array {
        // 1. Try to find by google_id
        $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = ? LIMIT 1");
        $stmt->execute([$googleUser['google_id']]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // 2. Try to find by email (link existing account)
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$googleUser['email']]);
        $user = $stmt->fetch();
        if ($user) {
            // Attach google_id, avatar and refresh_token to existing account
            $this->db->prepare("UPDATE users SET google_id = ?, avatar = ?, google_refresh_token = COALESCE(?, google_refresh_token) WHERE id = ?")
                ->execute([
                    $googleUser['google_id'], 
                    $googleUser['avatar'] ?? null, 
                    $googleUser['refresh_token'] ?? null,
                    $user['id']
                ]);
            return array_merge($user, [
                'google_id' => $googleUser['google_id'],
                'avatar'    => $googleUser['avatar'] ?? $user['avatar'],
                'google_refresh_token' => $googleUser['refresh_token'] ?? $user['google_refresh_token']
            ]);
        }

        // 3. Create new Google-only account
        $this->db->prepare(
            "INSERT INTO users (name, email, password, role, avatar, google_id, google_refresh_token) VALUES (?, ?, NULL, 'faculty', ?, ?, ?)"
        )->execute([
            $googleUser['name'],
            $googleUser['email'],
            $googleUser['avatar'] ?? null,
            $googleUser['google_id'],
            $googleUser['refresh_token'] ?? null
        ]);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$this->db->lastInsertId()]);
        return $stmt->fetch();
    }

    public function updateRefreshToken(int $id, string $token): bool {
        $stmt = $this->db->prepare("UPDATE users SET google_refresh_token = ? WHERE id = ?");
        return $stmt->execute([$token, $id]);
    }

    public function getMasterRefreshToken(): ?string {
        // We look for the first admin who has a refresh token
        $stmt = $this->db->query("SELECT google_refresh_token FROM users WHERE role = 'admin' AND google_refresh_token IS NOT NULL LIMIT 1");
        return $stmt->fetchColumn() ?: null;
    }
}
