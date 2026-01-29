<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Database\Connection;
use PDO;

final class GameTemplateRepository
{
    private PDO $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM game_templates ORDER BY name');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM game_templates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
