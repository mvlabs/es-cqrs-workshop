<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\DomainEvent;

use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\AggregateChanged;

final class PizzeriaCreated extends AggregateChanged
{
    public static function fromIdAndName(
        PizzeriaId $id,
        string $name
    ): self
    {
        return self::occur(
            (string) $id,
            [
                'name' => $name
            ]
        );
    }

    public function pizzeriaId(): PizzeriaId
    {
        return PizzeriaId::fromString($this->aggregateId());
    }

    public function name(): string
    {
        return (string) $this->payload()['name'];
    }
}
