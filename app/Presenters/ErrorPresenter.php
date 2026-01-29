<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;

final class ErrorPresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->setView('default');
    }
}
