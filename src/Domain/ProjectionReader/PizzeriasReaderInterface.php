<?php

namespace MVLabs\EsCqrsWorkshop\Domain\ProjectionReader;

interface PizzeriasReaderInterface
{
    public function listPizzerias(): array;
}
