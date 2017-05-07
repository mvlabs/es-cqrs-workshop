<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

use MVLabs\EsCqrsWorkshop\Action\Home;
use MVLabs\EsCqrsWorkshop\Action\OrderNewPizza;
use Zend\Expressive\Application;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Stratigility\Middleware\ErrorHandler;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

// Self-called anonymous function that creates its own scope and keep the global namespace clean
(function () {
    $container = require __DIR__ . '/../config/container.php';

    $app = new Application(
        new FastRouteRouter(),
        $container
    );

    // The error handler should be the first (most outer) middleware to catch all Exceptions.
    $app->pipe(ErrorHandler::class);

    $app->pipeRoutingMiddleware();
    $app->pipeDispatchMiddleware();

    $app->get('/', Home::class);

    $app->post('/order-new-pizza', OrderNewPizza::class);

    $app->run();
})();