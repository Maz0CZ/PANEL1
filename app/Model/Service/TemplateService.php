<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Repository\GameTemplateRepository;

final class TemplateService
{
    public function __construct(private GameTemplateRepository $repository)
    {
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function get(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function namesById(): array
    {
        $map = [];
        foreach ($this->repository->all() as $template) {
            $map[(int) $template['id']] = $template['name'];
        }

        return $map;
    }
}
