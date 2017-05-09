<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Repository;

use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Repository\PizzeriasInterface;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

final class EventSourcedPizzerias implements PizzeriasInterface
{
    /**
     * @var AggregateRepository
     */
    private $aggregateRepository;

    public function __construct(AggregateRepository $aggregateRepository)
    {
        $this->aggregateRepository = $aggregateRepository;
    }

    public function add(Pizzeria $pizzeria): void
    {
        $this->aggregateRepository->saveAggregateRoot($pizzeria);
    }

    public function get(PizzeriaId $id): Pizzeria
    {
        $this->aggregateRepository->getAggregateRoot((string) $id);
    }
}
