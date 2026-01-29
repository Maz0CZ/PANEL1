<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Database\Connection;
use PDO;

final class FtpAccountRepository
{
    private PDO $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function create(int $userId, int $serverId, string $username, string $passwordHash, string $homeDir): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO ftp_accounts (user_id, server_id, username, password_hash, home_dir) VALUES (:user_id, :server_id, :username, :password_hash, :home_dir)');
        $stmt->execute([
            'user_id' => $userId,
            'server_id' => $serverId,
            'username' => $username,
            'password_hash' => $passwordHash,
            'home_dir' => $homeDir,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByServerId(int $serverId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM ftp_accounts WHERE server_id = :server_id');
        $stmt->execute(['server_id' => $serverId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
