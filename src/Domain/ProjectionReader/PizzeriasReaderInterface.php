<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\ProjectionReader;

interface PizzeriasReaderInterface
{
    public function listPizzerias(): array;
}
