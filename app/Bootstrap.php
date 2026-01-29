<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

final class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = dirname(__DIR__);

        $configurator->setTempDirectory($appDir . '/storage/temp');
        $configurator->addConfig($appDir . '/config/common.neon');
        $configurator->addConfig($appDir . '/config/local.neon');

        return $configurator->createContainer();
    }
}
