<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Repository\ServerRepository;

final class UserLimitService
{
    public function __construct(private ServerRepository $serverRepository)
    {
    }

    public function canCreateServer(int $userId): bool
    {
        return $this->serverRepository->countByUser($userId) < 1;
    }
}
