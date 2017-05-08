<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Repository;

use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;

interface PizzeriasInterface
{
    public function get(PizzeriaId $id): Pizzeria;

    public function add(Pizzeria $pizzeria): void;
}
