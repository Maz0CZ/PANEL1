<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Repository\ServerRepository;
use App\Model\Repository\UserRepository;
use App\Model\Service\TemplateService;

final class HomepagePresenter extends BasePresenter
{
    public function __construct(
        UserRepository $userRepository,
        private ServerRepository $serverRepository,
        private TemplateService $templateService,
    ) {
        parent::__construct($userRepository);
    }

    public function renderDefault(): void
    {
        $this->requireLogin();

        $userId = $this->getUserId();
        $this->template->servers = $userId ? $this->serverRepository->findByUser($userId) : [];
        $this->template->templateNames = $this->templateService->namesById();
    }
}
