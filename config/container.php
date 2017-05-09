<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

use Interop\Container\ContainerInterface;
use MVLabs\EsCqrsWorkshop\Action\CreatePizzeria;
use MVLabs\EsCqrsWorkshop\Action\Home;
use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Command\CreatePizzeria as CreatePizzeriaCommand;
use MVLabs\EsCqrsWorkshop\Domain\Repository\PizzeriasInterface;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\HtmlRenderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\Renderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Repository\EventSourcedPizzerias;
use Prooph\Common\Event\ActionEvent;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;
use Prooph\EventStoreBusBridge\EventPublisher;
use Prooph\EventStoreBusBridge\TransactionManager;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\AbstractPlugin;
use Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy;
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
        CreatePizzeria::class => function (ContainerInterface $container): CreatePizzeria {
            return new CreatePizzeria(
                $container->get(CommandBus::class)
            );
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
                        },
                        150000
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
        },
        EventStore::class => function (ContainerInterface $container): EventStore {
            $eventStore = new PostgresEventStore(
                new FQCNMessageFactory(),
                $container->get(\PDO::class),
                new PostgresSingleStreamStrategy(),
                1000
            );

            $wrapper = new TransactionalActionEventEmitterEventStore(
                $eventStore,
                new ProophActionEventEmitter([
                    TransactionalActionEventEmitterEventStore::EVENT_APPEND_TO,
                    TransactionalActionEventEmitterEventStore::EVENT_CREATE,
                    TransactionalActionEventEmitterEventStore::EVENT_LOAD,
                    TransactionalActionEventEmitterEventStore::EVENT_LOAD_REVERSE,
                    TransactionalActionEventEmitterEventStore::EVENT_DELETE,
                    TransactionalActionEventEmitterEventStore::EVENT_HAS_STREAM,
                    TransactionalActionEventEmitterEventStore::EVENT_FETCH_STREAM_METADATA,
                    TransactionalActionEventEmitterEventStore::EVENT_UPDATE_STREAM_METADATA,
                    TransactionalActionEventEmitterEventStore::EVENT_FETCH_STREAM_NAMES,
                    TransactionalActionEventEmitterEventStore::EVENT_FETCH_STREAM_NAMES_REGEX,
                    TransactionalActionEventEmitterEventStore::EVENT_FETCH_CATEGORY_NAMES,
                    TransactionalActionEventEmitterEventStore::EVENT_FETCH_CATEGORY_NAMES_REGEX,
                    TransactionalActionEventEmitterEventStore::EVENT_BEGIN_TRANSACTION,
                    TransactionalActionEventEmitterEventStore::EVENT_COMMIT,
                    TransactionalActionEventEmitterEventStore::EVENT_ROLLBACK,
                ])
            );

            $eventBus = new EventBus();
            (new OnEventStrategy())->attachToMessageBus($eventBus);

            (new EventPublisher($eventBus))->attachToEventStore($wrapper);

            return $wrapper;
        },
        \PDO::class => function (ContainerInterface $container): \PDO {
            return new \PDO(
                'pgsql:host=postgres;port=5432;dbname=mvlabs;options=\'--client_encoding=utf8\';',
                'mvlabs',
                'mvlabs'
            );
        },
        PizzeriasInterface::class => function (ContainerInterface $container): PizzeriasInterface {
            return new EventSourcedPizzerias(
                new AggregateRepository(
                    $container->get(EventStore::class),
                    AggregateType::fromAggregateRootClass(Pizzeria::class),
                    new AggregateTranslator()
                )
            );
        },

        // COMMANDS
        CreatePizzeriaCommand::class => function (ContainerInterface $container) : callable {
            $pizzerias = $container->get(PizzeriasInterface::class);

            return function (CreatePizzeriaCommand $createPizzeria) use ($pizzerias): void {
                $pizzerias->add(Pizzeria::new($createPizzeria->name()));
            };
        }
    ],
]);
