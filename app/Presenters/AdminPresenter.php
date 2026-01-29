<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Repository\GameTemplateRepository;
use App\Model\Repository\UserRepository;

final class AdminPresenter extends BasePresenter
{
    public function __construct(
        UserRepository $userRepository,
        private GameTemplateRepository $templateRepository,
    ) {
        parent::__construct($userRepository);
    }

    public function renderDefault(): void
    {
        $this->requireLogin();
        if (($this->identity['role'] ?? '') !== 'admin') {
            $this->flashMessage('Přístup odepřen.', 'danger');
            $this->redirect('Homepage:default');
        }

        $this->template->users = $this->userRepository->all();
        $this->template->templates = $this->templateRepository->all();
    }
}
