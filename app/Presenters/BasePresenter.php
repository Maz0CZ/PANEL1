<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Repository\UserRepository;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    protected ?array $identity = null;

    public function __construct(protected UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function startup(): void
    {
        parent::startup();

        $session = $this->getSession('auth');
        $userId = $session->get('userId');
        if ($userId !== null) {
            $this->identity = $this->userRepository->findById((int) $userId);
            $this->template->currentUser = $this->identity;
        }
    }

    protected function requireLogin(): void
    {
        if ($this->identity === null) {
            $this->flashMessage('Pro pokračování se přihlaste.', 'warning');
            $this->redirect('Sign:in');
        }
    }

    protected function getUserId(): ?int
    {
        return $this->identity ? (int) $this->identity['id'] : null;
    }
}
