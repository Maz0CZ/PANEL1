<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Repository\FtpAccountRepository;
use App\Model\Repository\ServerRepository;
use App\Model\Repository\UserRepository;
use App\Model\Service\ConsoleService;
use App\Model\Service\ProcessService;
use App\Model\Service\TemplateService;
use App\Model\Service\UserLimitService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

final class ServerPresenter extends BasePresenter
{
    public function __construct(
        UserRepository $userRepository,
        private ServerRepository $serverRepository,
        private FtpAccountRepository $ftpAccountRepository,
        private TemplateService $templateService,
        private ProcessService $processService,
        private ConsoleService $consoleService,
        private UserLimitService $userLimitService,
        private string $logDir,
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

    public function renderCreate(): void
    {
        $this->requireLogin();
        $this->template->templates = $this->templateService->all();
    }

    public function renderDetail(int $id): void
    {
        $this->requireLogin();
        $server = $this->loadServer($id);
        $this->template->server = $server;
        $this->template->templateName = $this->templateService->get((int) $server['game_type'])['name'] ?? 'Unknown';
        $this->template->ftp = $this->ftpAccountRepository->findByServerId($id);
        $this->template->console = $this->consoleService->tail($this->logFile($id));
    }

    public function handleStart(int $id): void
    {
        $this->requireLogin();
        $server = $this->loadServer($id);
        $template = $this->templateService->get((int) $server['game_type']);
        if (!$template) {
            $this->flashMessage('Šablona nenalezena.', 'danger');
            $this->redirect('detail', ['id' => $id]);
        }

        $this->processService->start($this->sessionName($id), $template['start_command'], $server['directory'], $this->logFile($id));
        $this->serverRepository->updateStatus($id, 'running', null);
        $this->consoleService->append($this->logFile($id), '[SYSTEM] Server spuštěn.');
        $this->redirect('detail', ['id' => $id]);
    }

    public function handleStop(int $id): void
    {
        $this->requireLogin();
        $this->loadServer($id);
        $this->processService->stop($this->sessionName($id));
        $this->serverRepository->updateStatus($id, 'stopped', null);
        $this->consoleService->append($this->logFile($id), '[SYSTEM] Server zastaven.');
        $this->redirect('detail', ['id' => $id]);
    }

    public function handleSendCommand(int $id, string $command): void
    {
        $this->requireLogin();
        $this->loadServer($id);
        $this->processService->sendCommand($this->sessionName($id), $command);
        $this->consoleService->append($this->logFile($id), sprintf('[CMD] %s', $command));
        $this->redirect('detail', ['id' => $id]);
    }

    protected function createComponentCreateServerForm(): Form
    {
        $form = new Form();
        $form->addSelect('template', 'Hra', $this->templateOptions())
            ->setPrompt('Vyberte hru')
            ->setRequired();
        $form->addInteger('port', 'Port')->setRequired();
        $form->addText('directory', 'Adresář')
            ->setRequired()
            ->setDefaultValue('/srv/ultimatepanel/server_' . ($this->getUserId() ?? 0));
        $form->addSubmit('send', 'Vytvořit server');
        $form->onSuccess[] = [$this, 'createServerSucceeded'];

        return $form;
    }

    public function createServerSucceeded(Form $form, array $values): void
    {
        $this->requireLogin();
        $userId = $this->getUserId();
        if ($userId === null) {
            $this->redirect('Sign:in');
        }

        if (!$this->userLimitService->canCreateServer($userId)) {
            $this->flashMessage('Každý uživatel může mít pouze jeden server.', 'warning');
            $this->redirect('default');
        }

        $template = $this->templateService->get((int) $values['template']);
        if ($template === null) {
            $this->flashMessage('Šablona nenalezena.', 'danger');
            $this->redirect('create');
        }

        if (!is_dir($values['directory'])) {
            mkdir($values['directory'], 0755, true);
        }

        $serverId = $this->serverRepository->create(
            $userId,
            (string) $values['template'],
            (int) $values['port'],
            $values['directory'],
            'stopped',
            null,
        );

        $ftpPassword = bin2hex(random_bytes(6));
        $ftpUsername = 'srv' . $serverId;
        $this->ftpAccountRepository->create(
            $userId,
            $serverId,
            $ftpUsername,
            password_hash($ftpPassword, PASSWORD_DEFAULT),
            $values['directory'],
        );

        $this->consoleService->append($this->logFile($serverId), '[SYSTEM] Server vytvořen.');
        $this->flashMessage('Server vytvořen. FTP heslo: ' . $ftpPassword, 'success');
        $this->redirect('detail', ['id' => $serverId]);
    }

    private function loadServer(int $id): array
    {
        $server = $this->serverRepository->findById($id);
        if (!$server || $server['user_id'] !== $this->getUserId()) {
            throw new BadRequestException('Server not found');
        }

        return $server;
    }

    private function sessionName(int $id): string
    {
        return 'srv_' . $id;
    }

    private function logFile(int $id): string
    {
        return $this->logDir . '/server_' . $id . '.log';
    }

    private function templateOptions(): array
    {
        $options = [];
        foreach ($this->templateService->all() as $template) {
            $options[$template['id']] = $template['name'];
        }

        return $options;
    }
}
