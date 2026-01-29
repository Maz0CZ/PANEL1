<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Database\Connection;
use DateTimeImmutable;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(string $email, string $passwordHash, string $role): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :password_hash, :role, :created_at)');
        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'created_at' => (new DateTimeImmutable())->format('c'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
}
