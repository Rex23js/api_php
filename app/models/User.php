<?php

require_once __DIR__ . '/../../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ── CRUD ────────────────────────────────────────

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email) VALUES (:name, :email)'
        );
        $stmt->execute([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = :name, email = :email WHERE id = :id'
        );
        return $stmt->execute([
            'name'  => $data['name'],
            'email' => $data['email'],
            'id'    => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
