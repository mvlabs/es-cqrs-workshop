<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

use Interop\Container\ContainerInterface;
use MVLabs\EsCqrsWorkshop\Action\Home;
use MVLabs\EsCqrsWorkshop\Action\OrderNewPizza;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\Renderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\HtmlRenderer;
use Prooph\Common\Event\ActionEvent;
use Prooph\EventStore\EventStore;
use Prooph\EventStoreBusBridge\TransactionManager;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\Plugin\ServiceLocatorPlugin;
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
        Home::class => function (ContainerInterface $container): Home {
            return new Home(
                $container->get(Renderer::class)
            );
        },
        OrderNewPizza::class => function (ContainerInterface $container): OrderNewPizza {
            return new OrderNewPizza();
        },

        // INFRASTRUCTURE
        Renderer::class => function (ContainerInterface $container): Renderer {
            return new HtmlRenderer(__DIR__ . '/../templates/');
        },
        CommandBus::class => function (ContainerInterface $container): CommandBus {
            $commandBus = new CommandBus();

            // this plugin says that the handler for a command is called as the
            // command itself. This allows to use the command name as a key for the
            // factory of the command handler in the configuration
            (new class extends AbstractPlugin {
                public function attachToMessageBus(MessageBus $messageBus): void
                {
                    $this->listenerHandlers[] = $messageBus->attach(
                        MessageBus::EVENT_DISPATCH,
                        function (ActionEvent $event) {
                            $event->setParam(
                                MessageBus::EVENT_PARAM_MESSAGE_HANDLER,
                                (string) $event->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME)
                            );
                        }
                    );
                }
            })->attachToMessageBus($commandBus);

            // this plugin is used to retrieve from the dependency injection
            // container the handler for the command
            (new ServiceLocatorPlugin($container))->attachToMessageBus($commandBus);

            // this plugin enables transaction handling based on command dispatch
            (new TransactionManager(
                $container->get(EventStore::class)
            ))->attachToMessageBus($commandBus);

            return $commandBus;
        }
    ]
]);
