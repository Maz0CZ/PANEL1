<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Database\Connection;
use DateTimeImmutable;
use PDO;

final class ServerRepository
{
    private PDO $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function create(int $userId, string $gameType, int $port, string $directory, string $status, ?int $pid): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO servers (user_id, game_type, port, directory, status, pid, created_at) VALUES (:user_id, :game_type, :port, :directory, :status, :pid, :created_at)');
        $stmt->execute([
            'user_id' => $userId,
            'game_type' => $gameType,
            'port' => $port,
            'directory' => $directory,
            'status' => $status,
            'pid' => $pid,
            'created_at' => (new DateTimeImmutable())->format('c'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?int $pid): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET status = :status, pid = :pid WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'pid' => $pid,
        ]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM servers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM servers WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM servers WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
