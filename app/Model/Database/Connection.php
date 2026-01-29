<?php

declare(strict_types=1);

namespace App\Model\Database;

use PDO;
use PDOException;

final class Connection
{
    private PDO $pdo;

    public function __construct(string $databasePath)
    {
        $dsn = sprintf('sqlite:%s', $databasePath);
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
