<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList();
        $router->addRoute('sign/<action>', 'Sign:in');
        $router->addRoute('server/<action>[/<id>]', 'Server:default');
        $router->addRoute('admin/<action>[/<id>]', 'Admin:default');
        $router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }
}
