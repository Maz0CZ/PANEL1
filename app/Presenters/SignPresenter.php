<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Mail\RegistrationMailer;
use App\Model\Repository\UserRepository;
use Nette\Application\UI\Form;
use Nette\Utils\Validators;

final class SignPresenter extends BasePresenter
{
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        UserRepository $userRepository,
        private RegistrationMailer $registrationMailer,
    ) {
        parent::__construct($userRepository);
    }

    public function renderIn(): void
    {
        if ($this->identity) {
            $this->redirect('Homepage:default');
        }
    }

    public function renderRegister(): void
    {
        if ($this->identity) {
            $this->redirect('Homepage:default');
        }
    }

    public function actionOut(): void
    {
        $this->getSession('auth')->remove();
        $this->flashMessage('Byli jste odhlášeni.', 'info');
        $this->redirect('Sign:in');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addEmail('email', 'Email')->setRequired();
        $form->addPassword('password', 'Heslo')->setRequired();
        $form->addSubmit('send', 'Přihlásit');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, array $values): void
    {
        $attempts = $this->getLoginAttempts();
        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->flashMessage('Příliš mnoho pokusů o přihlášení. Zkuste to později.', 'danger');
            return;
        }

        $user = $this->userRepository->findByEmail($values['email']);
        if ($user === null || !password_verify($values['password'], $user['password_hash'])) {
            $this->incrementLoginAttempts();
            $this->flashMessage('Neplatné přihlašovací údaje.', 'danger');
            return;
        }

        $this->resetLoginAttempts();
        $this->getSession('auth')->set('userId', (int) $user['id']);
        $this->flashMessage('Vítejte zpět!', 'success');
        $this->redirect('Homepage:default');
    }

    protected function createComponentRegisterForm(): Form
    {
        $form = new Form();
        $form->addEmail('email', 'Email')->setRequired();
        $form->addPassword('password', 'Heslo')
            ->setRequired()
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8);
        $form->addSubmit('send', 'Registrovat');
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }

    public function registerFormSucceeded(Form $form, array $values): void
    {
        if (!Validators::isEmail($values['email'])) {
            $this->flashMessage('Neplatný email.', 'danger');
            return;
        }

        if ($this->userRepository->findByEmail($values['email'])) {
            $this->flashMessage('Email je již registrován.', 'warning');
            return;
        }

        $hash = password_hash($values['password'], PASSWORD_DEFAULT);
        $this->userRepository->create($values['email'], $hash, 'user');
        $this->registrationMailer->sendWelcome($values['email']);

        $this->flashMessage('Registrace byla úspěšná. Přihlaste se.', 'success');
        $this->redirect('Sign:in');
    }

    private function getLoginAttempts(): int
    {
        return (int) ($this->getSession('auth')->get('attempts') ?? 0);
    }

    private function incrementLoginAttempts(): void
    {
        $attempts = $this->getLoginAttempts();
        $this->getSession('auth')->set('attempts', $attempts + 1);
    }

    private function resetLoginAttempts(): void
    {
        $this->getSession('auth')->remove('attempts');
    }
}
