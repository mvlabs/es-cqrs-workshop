<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Snapshot;

use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Repository\PizzeriasInterface;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\Common\Event\ActionEvent;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\Plugin\AbstractPlugin;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;
use Prooph\ServiceBus\EventBus;
use Prooph\SnapshotStore\Snapshot;
use Prooph\SnapshotStore\SnapshotStore;

final class PizzeriaSnapshot extends AbstractPlugin
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var PizzeriasInterface
     */
    private $pizzeriasRepository;

    /**
     * @var SnapshotStore
     */
    private $snapshotStore;

    /**
     * @var \Iterator[]
     */
    private $cachedEventStreams = [];

    public function __construct(
        EventBus $eventBus,
        PizzeriasInterface $pizzeriasRepository,
        SnapshotStore $snapshotStore
    ) {
        $this->eventBus = $eventBus;
        $this->pizzeriasRepository = $pizzeriasRepository;
        $this->snapshotStore = $snapshotStore;
    }

    public function attachToEventStore(ActionEventEmitterEventStore $eventStore): void
    {
        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_APPEND_TO,
            function (ActionEvent $event) use ($eventStore): void {
                $recordedEvents = $event->getParam('streamEvents', new \ArrayIterator());

                $this->cachedEventStreams[] = $recordedEvents;
            }
        );

        $this->listenerHandlers[] = $eventStore->attach(
            ActionEventEmitterEventStore::EVENT_CREATE,
            function (ActionEvent $event) use ($eventStore): void {
                $stream = $event->getParam('stream');
                $recordedEvents = $stream->streamEvents();

                $this->cachedEventStreams[] = $recordedEvents;
            }
        );

        $this->listenerHandlers[] = $eventStore->attach(
            TransactionalActionEventEmitterEventStore::EVENT_COMMIT,
            function (ActionEvent $event): void {
                $snapshots = [];

                foreach ($this->cachedEventStreams as $stream) {
                    /* @var $recordedEvent \Prooph\Common\Messaging\Message */
                    foreach ($stream as $recordedEvent) {
                        $metadata = $recordedEvent->metadata();

                        $doSnapshot = $metadata['_aggregate_version'] % 5 === 0; // do a snapshot every 5 events

                        if (false === $doSnapshot || !isset($metadata['_aggregate_type'], $metadata['_aggregate_id'])) {
                            continue;
                        }
                        $snapshots[$metadata['_aggregate_type']][] = $metadata['_aggregate_id'];
                    }
                }

                foreach ($snapshots as $aggregateType => $aggregateIds) {
                    foreach ($aggregateIds as $aggregateId) {
                        $pizzeriaId = PizzeriaId::fromString($aggregateId);

                        $pizzeria = $this->pizzeriasRepository->get($pizzeriaId);

                        $snapshot = new Snapshot(
                            AggregateType::fromAggregateRoot($pizzeria)->toString(),
                            (string) $pizzeriaId,
                            $pizzeria,
                            $this->getVersion($pizzeria),
                            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
                        );

                        $this->snapshotStore->save($snapshot);
                    }
                }
            }
        );

        $this->listenerHandlers[] = $eventStore->attach(
            TransactionalActionEventEmitterEventStore::EVENT_ROLLBACK,
            function (ActionEvent $event): void {
                $this->cachedEventStreams = [];
            }
        );
    }

    private function getVersion(Pizzeria $pizzeria): int
    {
        $reflectionClass = new \ReflectionClass($pizzeria);
        $versionProp = $reflectionClass->getProperty('version');
        $versionProp->setAccessible(true);

        return $versionProp->getValue($pizzeria);
    }
}
