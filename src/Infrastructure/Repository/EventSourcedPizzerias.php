<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Repository;

use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Repository\PizzeriasInterface;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;

final class EventSourcedPizzerias implements PizzeriasInterface
{
    public function add(Pizzeria $pizzeria): void
    {
        // TODO: Implement add() method.
    }

    public function get(PizzeriaId $id): Pizzeria
    {
        // TODO: Implement get() method.
    }
}
