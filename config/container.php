<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop;

use Interop\Container\ContainerInterface;
use MVLabs\EsCqrsWorkshop\Action\CompleteOrder;
use MVLabs\EsCqrsWorkshop\Action\CreatePizzeria;
use MVLabs\EsCqrsWorkshop\Action\ComposeOrder;
use MVLabs\EsCqrsWorkshop\Action\Home;
use MVLabs\EsCqrsWorkshop\Action\OrdersList;
use MVLabs\EsCqrsWorkshop\Action\PizzeriasList;
use MVLabs\EsCqrsWorkshop\Action\SendOrder;
use MVLabs\EsCqrsWorkshop\Action\ShowOrders;
use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Command\CreatePizzeria as CreatePizzeriaCommand;
use MVLabs\EsCqrsWorkshop\Domain\Command\AddOrder as AddOrderCommand;
use MVLabs\EsCqrsWorkshop\Domain\Command\NotifyDeliveryBoy;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderCompleted;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderReceived;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\PizzeriaCreated;
use MVLabs\EsCqrsWorkshop\Domain\Process\WhenOrderCompletedNotifyDeliveryBoy;
use MVLabs\EsCqrsWorkshop\Domain\ProjectionReader\PizzeriasReaderInterface;
use MVLabs\EsCqrsWorkshop\Domain\Repository\PizzeriasInterface;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use MVLabs\EsCqrsWorkshop\Infrastructure\Projector\RecordPizzeriaOnOrderCompleted;
use MVLabs\EsCqrsWorkshop\Infrastructure\Projector\RecordPizzeriaOnOrderReceived;
use MVLabs\EsCqrsWorkshop\Infrastructure\Projector\RecordPizzeriaOnPizzeriaCreated;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\HtmlRenderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Renderer\Renderer;
use MVLabs\EsCqrsWorkshop\Infrastructure\Repository\EventSourcedPizzerias;
use MVLabs\EsCqrsWorkshop\Infrastructure\ProjectionReader\PizzeriasReader;
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
        // ERROR HANDLER
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
        ComposeOrder::class => function (ContainerInterface $container): ComposeOrder {
            return new ComposeOrder(
                $container->get(Renderer::class)
            );
        },
        PizzeriasList::class => function (ContainerInterface $container): PizzeriasList {
            return new PizzeriasList(
                $container->get(PizzeriasReaderInterface::class)
            );
        },
        SendOrder::class => function (ContainerInterface $container): SendOrder {
            return new SendOrder(
                $container->get(CommandBus::class)
            );
        },
        ShowOrders::class => function (ContainerInterface $container): ShowOrders {
            return new ShowOrders(
                $container->get(Renderer::class)
            );
        },
        OrdersList::class => function (ContainerInterface $container): OrdersList {
            return new OrdersList(
                $container->get(PizzeriasReaderInterface::class)
            );
        },
        CompleteOrder::class => function (ContainerInterface $container): CompleteOrder {
            return new CompleteOrder(
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
            (new class($container) extends AbstractPlugin {
                /**
                 * @var ContainerInterface
                 */
                private $container;

                public function __construct(ContainerInterface $container)
                {
                    $this->container = $container;
                }

                public function attachToMessageBus(MessageBus $messageBus): void
                {
                    $this->listenerHandlers[] = $messageBus->attach(
                        MessageBus::EVENT_DISPATCH,
                        function (ActionEvent $actionEvent): void {
                            if ($actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_HANDLED, false)) {
                                return;
                            }

                            $messageName = $actionEvent->getParam(MessageBus::EVENT_PARAM_MESSAGE_NAME);

                            $handlers = [];

                            if ($this->container->has($messageName)) {
                                $handlers = $this->container->get($messageName);
                            }

                            $actionEvent->setParam(EventBus::EVENT_PARAM_EVENT_LISTENERS, $handlers);
                        },
                        MessageBus::PRIORITY_LOCATE_HANDLER
                    );
                }
            })->attachToMessageBus($eventBus);

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

        // PROJECTION READERS
        PizzeriasReaderInterface::class => function (ContainerInterface $container): PizzeriasReaderInterface {
            return new PizzeriasReader($container->get(\PDO::class));
        },

        // COMMANDS
        CreatePizzeriaCommand::class => function (ContainerInterface $container): callable {
            $pizzerias = $container->get(PizzeriasInterface::class);

            return function (CreatePizzeriaCommand $createPizzeria) use ($pizzerias): void {
                $pizzerias->add(Pizzeria::new($createPizzeria->name()));
            };
        },
        AddOrderCommand::class => function (ContainerInterface $container): callable {
            /** @var $pizzerias PizzeriasInterface */
            $pizzerias = $container->get(PizzeriasInterface::class);

            return function (AddOrderCommand $addOrder) use ($pizzerias): void {
                $pizzeria = $pizzerias->get($addOrder->pizzeriaId());

                $pizzeria->addOrder($addOrder->customerName(), $addOrder->pizzaTaste());

                $pizzerias->add($pizzeria);
            };
        },
        \MVLabs\EsCqrsWorkshop\Domain\Command\CompleteOrder::class => function (ContainerInterface $container): callable {
            /** @var $pizzerias PizzeriasInterface */
            $pizzerias = $container->get(PizzeriasInterface::class);

            return function (\MVLabs\EsCqrsWorkshop\Domain\Command\CompleteOrder $completeOrder) use ($pizzerias): void {
                $pizzeria = $pizzerias->get($completeOrder->pizzeriaId());

                $pizzeria->completeOrder(
                    $completeOrder->customerName(),
                    $completeOrder->pizzaTaste(),
                    $completeOrder->orderCreatedAt()
                );

                $pizzerias->add($pizzeria);
            };
        },
        NotifyDeliveryBoy::class => function (ContainerInterface $container): callable {
            return function (NotifyDeliveryBoy $notifyDeliveryBoy): void {};
        },

        // EVENTS
        PizzeriaCreated::class => function (ContainerInterface $container): array {
            return [
                new RecordPizzeriaOnPizzeriaCreated($container->get(\PDO::class))
            ];
        },
        OrderReceived::class => function (ContainerInterface $container): array {
            return [
                new RecordPizzeriaOnOrderReceived($container->get(\PDO::class))
            ];
        },
        OrderCompleted::class => function (ContainerInterface $container): array {
            return [
                new RecordPizzeriaOnOrderCompleted($container->get(\PDO::class)),
                new WhenOrderCompletedNotifyDeliveryBoy($container->get(CommandBus::class))
            ];
        }
    ],
]);
