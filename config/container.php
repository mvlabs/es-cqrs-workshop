<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

use Interop\Container\ContainerInterface;
use MVLabs\EsCqrsWorkshop\Action\Home;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\Renderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\HtmlRenderer;
use Zend\Expressive\Container\ErrorHandlerFactory;
use Zend\Expressive\Container\WhoopsErrorResponseGeneratorFactory;
use Zend\Expressive\Container\WhoopsFactory;
use Zend\Expressive\Container\WhoopsPageHandlerFactory;
use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\ServiceManager\ServiceManager;
use Zend\Stratigility\Middleware\ErrorHandler;

return new ServiceManager([
    'factories' => [
        ErrorHandler::class => ErrorHandlerFactory::class,
        ErrorResponseGenerator::class => WhoopsErrorResponseGeneratorFactory::class,
        'Zend\Expressive\Whoops' => WhoopsFactory::class,
        'Zend\Expressive\WhoopsPageHandler' => WhoopsPageHandlerFactory::class,

        // ACTIONS
        Home::class => function (ContainerInterface $container) {
            return new Home(
                $container->get(Renderer::class)
            );
        },

        // INFRASTRUCTURE
        Renderer::class => function (ContainerInterface $container) {
            return new HtmlRenderer(__DIR__ . '/../templates/');
        }
    ]
]);

