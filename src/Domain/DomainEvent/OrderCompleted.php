<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\DomainEvent;

use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\AggregateChanged;

final class OrderCompleted extends AggregateChanged
{
    public static function fromCustomerPizzeriaAndPizzaTaste(
        string $customerName,
        PizzeriaId $id,
        string $pizzaTaste
    ): self
    {
        return self::occur(
            (string) $id,
            [
                'customerName' => $customerName,
                'pizzaTaste' => $pizzaTaste,
            ]
        );
    }

    public function pizzeriaId(): PizzeriaId
    {
        return PizzeriaId::fromString($this->aggregateId());
    }

    public function customerName(): string
    {
        return (string) $this->payload()['customerName'];
    }

    public function pizzaTaste() : string
    {
        return (string) $this->payload()['pizzaTaste'];
    }
}
